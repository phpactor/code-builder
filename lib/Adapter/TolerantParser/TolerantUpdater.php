<?php 
namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\ConstDeclaration;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Util\TextFormat;

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

        // fast forward to last use declaration
        if ($lastNode instanceof NamespaceUseDeclaration) {
            $parent = $lastNode->parent;
            foreach ($parent->getChildNodes() as $child) {
                if ($child instanceof NamespaceUseDeclaration) {
                    $lastNode = $child;
                }
            }
        }

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
            $this->after($lastNode, 'use ' . (string) $usePrototype . ';');
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
        $this->updateConstants($classPrototype, $classNode);
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

    private function updateConstants(ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (count($classPrototype->constants()) === 0) {
            return;
        }

        $lastConstant = $classNode->classMembers->openBrace;
        $nextMember = null;

        $memberDeclarations = $classNode->classMembers->classMemberDeclarations;
        $existingConstantNames = [];
        foreach ($memberDeclarations as $memberNode) {
            if (null === $nextMember) {
                $nextMember = $memberNode;
            }

            if ($memberNode instanceof ClassConstDeclaration) {
                /** @var ConstDeclaration $memberNode */
                foreach ($memberNode->constElements->getElements() as $variable) {
                    $existingConstantNames[] = $variable->getName();
                }
                $lastConstant = $memberNode;
                $nextMember = next($memberDeclarations) ?: $nextMember;
                prev($memberDeclarations);
            }
        }

        foreach ($classPrototype->constants()->notIn($existingConstantNames) as $constant) {
            // if constant type exists then the last constant has a docblock - add a line break
            if ($lastConstant instanceof ConstantDeclaration && $constant->type() != Type::none()) {
                $this->after($lastConstant, PHP_EOL);
            }

            $this->after(
                $lastConstant,
                PHP_EOL . $this->textFormat->indent($this->renderer->render($constant), 1)
            );

            if ($classPrototype->constants()->isLast($constant) && (
                $nextMember instanceof MethodDeclaration ||
                $nextMember instanceof PropertyDeclaration
            )) {
                $this->after($lastConstant, PHP_EOL);
            }
        }
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
                foreach ($memberNode->propertyElements->getElements() as $property) {
                    $existingPropertyNames[] = $this->resolvePropertyName($property);
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
        $existingMethods = [];
        foreach ($memberDeclarations as $memberNode) {
            if ($memberNode instanceof PropertyDeclaration) {
                $lastMember = $memberNode;
                $newLine = true;
            }

            if ($memberNode instanceof MethodDeclaration) {
                $lastMember = $memberNode;
                $existingMethodNames[] = $memberNode->getName();
                $existingMethods[$memberNode->getName()] = $memberNode;
                $newLine = true;
            }
        }

        // Update methods
        $methods = $classPrototype->methods()->in($existingMethodNames);

        foreach ($methods as $method) {
            $methodDeclaration = $existingMethods[$method->name()];
            $bodyNode = $methodDeclaration->compoundStatementOrSemicolon;

            if ($method->body()->lines()->count()) {
                if ($bodyNode instanceof CompoundStatementNode) {
                    $lastStatement = end($bodyNode->statements) ?: $bodyNode->openBrace;

                    foreach ($method->body()->lines() as $line) {
                        // do not add duplicate lines
                        $bodyNodeLines = explode(PHP_EOL, $bodyNode->getText());

                        foreach ($bodyNodeLines as $bodyNodeLine) {
                            if (trim($bodyNodeLine) == trim((string) $line)) {
                                continue 2;
                            }
                        }

                        $this->after(
                            $lastStatement,
                            PHP_EOL . $this->textFormat->indent((string) $line, 2)
                        );
                    }
                }
            }
        }

        $methods = $classPrototype->methods()->notIn($existingMethodNames);

        if (0 === count($methods)) {
            return;
        }

        if ($newLine) {
            $this->after($lastMember, PHP_EOL);
        }

        // Add methods
        foreach ($methods as $method) {
            $this->after(
                $lastMember,
                PHP_EOL . $this->textFormat->indent($this->renderer->render($method) . PHP_EOL . $this->renderer->render($method->body()), 1)
            );

            if (false === $classPrototype->methods()->isLast($method)) {
                $this->after($lastMember, PHP_EOL);
            }
        }
    }

    private function resolvePropertyName(Node $property)
    {
        if ($property instanceof Variable) {
            return $property->getName();
        }

        if ($property instanceof AssignmentExpression) {
            return $this->resolvePropertyName($property->leftOperand);
        }

        throw new \InvalidArgumentException(sprintf(
            'Do not know how to resolve property elemnt of type "%s"',
            get_class($property)
        ));
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
