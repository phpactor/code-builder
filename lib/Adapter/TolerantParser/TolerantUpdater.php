<?php 
namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Code;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Phpactor\CodeBuilder\Domain\Renderer;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Token;
use Phpactor\CodeBuilder\Util\TextFormat;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;

class TolerantUpdater implements Updater
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var TextEdit[]
     */
    private $edits = [];

    /**
     * @var TextFormat
     */
    private $textFormat;

    public function __construct(Renderer $renderer, TextFormat $textFormat = null, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->textFormat = $textFormat ?: new TextFormat();
        $this->renderer = $renderer;
    }

    public function apply(Prototype $prototype, Code $code): Code
    {
        $this->edits = [];
        $node = $this->parser->parseSourceFile((string) $code);

        $this->updateNamespace($prototype, $node);
        $this->updateUseStatements($prototype, $node);
        $this->updateClasses($prototype, $node);

        return Code::fromString(trim(TextEdit::applyEdits($this->edits, (string) $code)));
    }

    private function updateNamespace(SourceCode $prototype, SourceFileNode $node)
    {
        $namespaceNode = $node->getFirstChildNode(NamespaceDefinition::class);

        if (null !== $namespaceNode && NamespaceName::root() == $prototype->namespace()) {
            return;
        }

        /** @var $namespaceNode NamespaceDefinition */
        if ($namespaceNode && $namespaceNode->name->getText() == (string) $prototype->namespace()) {
            return;
        }

        if (empty((string) $prototype->namespace())) {
            return;
        }

        if ($namespaceNode) {
            $this->replace($namespaceNode, 'namespace ' . (string) $prototype->namespace() . ';');
            return;
        }

        $startTag = $node->getFirstChildNode(InlineHtml::class);
        $this->after($startTag, 'namespace ' . (string) $prototype->namespace() . ';' . PHP_EOL.PHP_EOL);
    }

    private function updateUseStatements(SourceCode $prototype, SourceFileNode $node)
    {
        if (0 === count($prototype->useStatements())) {
            return;
        }

        $lastNode = $node->getFirstChildNode(NamespaceUseDeclaration::class, NamespaceDefinition::class, InlineHtml::class);

        $this->after($lastNode, PHP_EOL);

        if ($lastNode instanceof NamespaceDefinition) {
            $this->after($lastNode, PHP_EOL);
        }

        /** @var $usePrototype Type */
        foreach ($prototype->useStatements() as $usePrototype) {
            foreach ($node->getChildNodes() as $childNode) {
                if ($childNode instanceof NamespaceUseDeclaration) {
                    foreach ($childNode->useClauses->getElements() as $useClause) {
                        if ($useClause->namespaceName->getText() == $usePrototype->__toString()) {
                            continue 3;
                        }
                    }
                }
            }
            $this->after($lastNode, 'use ' . (string) $usePrototype . ';' . PHP_EOL);
        }
    }

    private function updateClasses(SourceCode $prototype, SourceFileNode $node)
    {
        $classNodes = [];
        $lastStatement = null;
        foreach ($node->statementList as $classNode) {
            $lastStatement = $classNode;
            if (!$classNode instanceof ClassDeclaration) {
                continue;
            }

            $name = $classNode->name->getText($node->getFileContents());
            $classNodes[$name] = $classNode;
        }

        foreach ($prototype->classes()->in(array_keys($classNodes)) as $classPrototype) {
            $this->updateClass($classPrototype, $classNodes[$classPrototype->name()]);
        }

        if (substr($lastStatement->getText(), -1) !== PHP_EOL) {
            $this->after($lastStatement, PHP_EOL);
        }

        $classes = $prototype->classes()->notIn(array_keys($classNodes));
        $index = 0;
        foreach ($classes as $classPrototype) {
            if ($index > 0 && $index + 1 == count($classes)) {
                $this->after($lastStatement, PHP_EOL);
            }
            $this->after($lastStatement, PHP_EOL . $this->renderer->render($classPrototype));
            $index++;
        }
    }

    private function updateClass(ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        $this->updateExtends($classPrototype, $classNode);
        $this->updateImplements($classPrototype, $classNode);
        $this->updateProperties($classPrototype, $classNode);
        $this->updateMethods($classPrototype, $classNode);
    }

    private function updateExtends(ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (ExtendsClass::none() == $classPrototype->extendsClass()) {
            return;
        }

        if (null === $classNode->classBaseClause) {
            $this->after($classNode->name, ' extends ' . (string) $classPrototype->extendsClass());
            return;
        }


        $this->replace($classNode->classBaseClause, ' extends ' . (string) $classPrototype->extendsClass());
    }

    private function updateImplements(ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (ImplementsInterfaces::empty() == $classPrototype->implementsInterfaces()) {
            return;
        }

        if (null === $classNode->classInterfaceClause) {
            $this->after($classNode->name, ' implements ' . (string) $classPrototype->implementsInterfaces()->__toString());
            return;
        }

        $existingNames = [];
        foreach ($classNode->classInterfaceClause->interfaceNameList->getElements() as $name) {
            $existingNames[] = $name->getText();
        }

        $additionalNames = $classPrototype->implementsInterfaces()->notIn($existingNames);

        if (0 === count($additionalNames)) {
            return;
        }

        $names = join(', ', [ implode(', ', $existingNames), $additionalNames->__toString()]);

        $this->replace($classNode->classInterfaceClause, ' implements ' . $names);
    }

    private function updateProperties(ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (count($classPrototype->properties()) === 0) {
            return;
        }

        $lastProperty = $classNode->classMembers->openBrace;
        $nextMember = null;

        $memberDeclarations = $classNode->classMembers->classMemberDeclarations;
        $existingPropertyNames = [];
        foreach ($memberDeclarations as $memberNode) {
            if (null === $nextMember) {
                $nextMember = $memberNode;
            }

            if ($memberNode instanceof PropertyDeclaration) {
                foreach ($memberNode->propertyElements->getElements() as $variable) {
                    $existingPropertyNames[] = $variable->getName();
                }
                $lastProperty = $memberNode;
                $nextMember = next($memberDeclarations) ?: $nextMember;
                prev($memberDeclarations);
            }
        }

        foreach ($classPrototype->properties()->notIn($existingPropertyNames) as $property) {
            // if property type exists then the last property has a docblock - add a line break
            if ($lastProperty instanceof PropertyDeclaration && $property->type() != Type::none()) {
                $this->after($lastProperty, PHP_EOL);
            }

            $this->after(
                $lastProperty,
                PHP_EOL . $this->textFormat->indent($this->renderer->render($property), 1)
            );

            if ($classPrototype->properties()->isLast($property) && $nextMember instanceof MethodDeclaration) {
                $this->after($lastProperty, PHP_EOL);
            }
        }
    }

    private function updateMethods(ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (count($classPrototype->methods()) === 0) {
            return;
        }

        $lastMember = $classNode->classMembers->openBrace;

        $newLine = false;
        $memberDeclarations = $classNode->classMembers->classMemberDeclarations;
        $existingMethodNames = [];
        foreach ($memberDeclarations as $memberNode) {
            if ($memberNode instanceof PropertyDeclaration) {
                $lastMember = $memberNode;
                $newLine = true;
            }

            if ($memberNode instanceof MethodDeclaration) {
                $lastMember = $memberNode;
                $existingMethodNames[] = $memberNode->getName();
                $newLine = true;
            }
        }

        $methods = $classPrototype->methods()->notIn($existingMethodNames);

        if (0 === count($methods)) {
            return;
        }

        if ($newLine) {
            $this->after($lastMember, PHP_EOL);
        }

        foreach ($methods as $method) {
            $this->after(
                $lastMember,
                PHP_EOL . $this->textFormat->indent($this->renderer->render($method) . PHP_EOL . '{' . PHP_EOL . '}', 1)
            );

            if (false === $classPrototype->methods()->isLast($method)) {
                $this->after($lastMember, PHP_EOL);
            }
        }
    }

    private function remove($node)
    {
        $this->edits[] = new TextEdit($node->getFullStart(), $node->getFullWidth(), '');
    }

    private function after($node, string $text)
    {
        $this->edits[] = new TextEdit($this->getEndPos($node), 0, $text);
    }

    private function replace($node, string $text)
    {
        $this->edits[] = new TextEdit($node->getFullStart(), $node->getFullWidth(), $text);
    }

    private function getEndPos($node)
    {
        return $node->getEndPosition();
    }
}
