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
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Prototype\Parameter;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;

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
        $edits = new Edits();
        $node = $this->parser->parseSourceFile((string) $code);

        $this->updateNamespace($edits, $prototype, $node);
        $this->updateUseStatements($edits, $prototype, $node);
        $this->updateClasses($edits, $prototype, $node);

        return Code::fromString($edits->apply((string) $code));
    }

    private function updateNamespace(Edits $edits, SourceCode $prototype, SourceFileNode $node)
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
            $edits->replace($namespaceNode, 'namespace ' . (string) $prototype->namespace() . ';');
            return;
        }

        $startTag = $node->getFirstChildNode(InlineHtml::class);
        $edits->after($startTag, 'namespace ' . (string) $prototype->namespace() . ';' . PHP_EOL.PHP_EOL);
    }

    private function updateUseStatements(Edits $edits, SourceCode $prototype, SourceFileNode $node)
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

        if ($lastNode instanceof NamespaceDefinition) {
            $edits->after($lastNode, PHP_EOL);
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

            $newUseStatement = PHP_EOL . 'use ' . (string) $usePrototype . ';';

            $edits->after($lastNode, $newUseStatement);
        }

        if ($lastNode instanceof InlineHtml) {
            $edits->after($lastNode, PHP_EOL . PHP_EOL);
        }
    }

    private function updateClasses(Edits $edits, SourceCode $prototype, SourceFileNode $node)
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
            $this->updateClass($edits, $classPrototype, $classNodes[$classPrototype->name()]);
        }

        if (substr($lastStatement->getText(), -1) !== PHP_EOL) {
            $edits->after($lastStatement, PHP_EOL);
        }

        $classes = $prototype->classes()->notIn(array_keys($classNodes));
        $index = 0;
        foreach ($classes as $classPrototype) {
            if ($index > 0 && $index + 1 == count($classes)) {
                $edits->after($lastStatement, PHP_EOL);
            }
            $edits->after($lastStatement, PHP_EOL . $this->renderer->render($classPrototype));
            $index++;
        }
    }

    private function updateClass(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        $this->updateExtends($edits, $classPrototype, $classNode);
        $this->updateImplements($edits, $classPrototype, $classNode);
        $this->updateConstants($edits, $classPrototype, $classNode);
        $this->updateProperties($edits, $classPrototype, $classNode);
        $this->updateMethods($edits, $classPrototype, $classNode);
    }

    private function updateExtends(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (ExtendsClass::none() == $classPrototype->extendsClass()) {
            return;
        }

        if (null === $classNode->classBaseClause) {
            $edits->after($classNode->name, ' extends ' . (string) $classPrototype->extendsClass());
            return;
        }


        $edits->replace($classNode->classBaseClause, ' extends ' . (string) $classPrototype->extendsClass());
    }

    private function updateImplements(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (ImplementsInterfaces::empty() == $classPrototype->implementsInterfaces()) {
            return;
        }

        if (null === $classNode->classInterfaceClause) {
            $edits->after($classNode->name, ' implements ' . (string) $classPrototype->implementsInterfaces()->__toString());
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

        $edits->replace($classNode->classInterfaceClause, ' implements ' . $names);
    }

    private function updateConstants(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
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
                $edits->after($lastConstant, PHP_EOL);
            }

            $edits->after(
                $lastConstant,
                PHP_EOL . $this->textFormat->indent($this->renderer->render($constant), 1)
            );

            if ($classPrototype->constants()->isLast($constant) && (
                $nextMember instanceof MethodDeclaration ||
                $nextMember instanceof PropertyDeclaration
            )) {
                $edits->after($lastConstant, PHP_EOL);
            }
        }
    }

    private function updateProperties(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
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
            // if property type exists and the last property has a docblock - add a line break
            if ($lastProperty instanceof PropertyDeclaration && $property->type() != Type::none()) {
                $edits->after($lastProperty, PHP_EOL);
            }

            $edits->after(
                $lastProperty,
                PHP_EOL . $this->textFormat->indent($this->renderer->render($property), 1)
            );

            if ($classPrototype->properties()->isLast($property) && $nextMember instanceof MethodDeclaration) {
                $edits->after($lastProperty, PHP_EOL);
            }
        }
    }

    private function updateMethods(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
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

        /** @var Method $method */
        foreach ($methods as $method) {
            /** @var MethodDeclaration $methodDeclaration */
            $methodDeclaration = $existingMethods[$method->name()];
            $bodyNode = $methodDeclaration->compoundStatementOrSemicolon;

            if ($method->body()->lines()->count()) {
                $this->appendLinesToMethod($edits, $method, $bodyNode);
            }

            $this->updateOrAddParameters($edits, $method->parameters(), $methodDeclaration);
        }

        $methods = $classPrototype->methods()->notIn($existingMethodNames);

        if (0 === count($methods)) {
            return;
        }

        if ($newLine) {
            $edits->after($lastMember, PHP_EOL);
        }

        // Add methods
        foreach ($methods as $method) {
            $edits->after(
                $lastMember,
                PHP_EOL . $this->textFormat->indent($this->renderer->render($method) . PHP_EOL . $this->renderer->render($method->body()), 1)
            );

            if (false === $classPrototype->methods()->isLast($method)) {
                $edits->after($lastMember, PHP_EOL);
            }
        }
    }

    private function appendLinesToMethod(Edits $edits, Method $method, Node $bodyNode)
    {
        if (false === $bodyNode instanceof CompoundStatementNode) {
            return;
        }

        $lastStatement = end($bodyNode->statements) ?: $bodyNode->openBrace;

        foreach ($method->body()->lines() as $line) {
            // do not add duplicate lines
            $bodyNodeLines = explode(PHP_EOL, $bodyNode->getText());

            foreach ($bodyNodeLines as $bodyNodeLine) {
                if (trim($bodyNodeLine) == trim((string) $line)) {
                    continue 2;
                }
            }

            $edits->after(
                $lastStatement,
                PHP_EOL . $this->textFormat->indent((string) $line, 2)
            );
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

    private function updateOrAddParameters(Edits $edits, Parameters $parameters, MethodDeclaration $methodDeclaration)
    {
        if (0 === $parameters->count()) {
            return;
        }

        $parameterNodes = [];
        if ($methodDeclaration->parameters) {
            $parameterNodes = iterator_to_array($methodDeclaration->parameters->getElements());
        }
        $replacementParameters = [];

        foreach ($parameters as $parameter) {
            $parameterNode = current($parameterNodes);

            if ($parameterNode) {
                $parameterNodeName = ltrim($parameterNode->variableName->getText($parameterNode->getFileContents()), '$');

                if ($parameterNodeName == $parameter->name()) {
                    $replacementParameters[] = $this->renderer->render($parameter);
                    array_shift($parameterNodes);
                    continue;
                }
            }

            $replacementParameters[] = $this->renderer->render($parameter);
        }

        foreach ($parameterNodes as $parameterNode) {
            $replacementParameters[] = $parameterNode->getText($parameterNode->getFileContents());
        }

        if ($methodDeclaration->parameters) {
            $edits->replace($methodDeclaration->parameters, implode(', ', $replacementParameters));
            return;
        }

        $edits->add(new TextEdit($methodDeclaration->openParen->getStartPosition() + 1, 0, implode(', ', $replacementParameters)));
    }

    private function updateParameters(Parameters $parameters, ParameterDeclarationList $parameterList)
    {
        $seenParameters = [];
        foreach ($parameters as $parameter) {
            $parameterString = $this->renderer->render($parameter);
            foreach ($parameterList->getElements() as $parameterNode) {
                $parameterNodeName = ltrim($parameterNode->variableName->getText($parameterNode->getFileContents()), '$');

                if ($parameterNodeName == $parameter->name()) {
                    $edits->replace($methodDeclaration->parameters, $parameterString);
                    $seenParameters[] = $parameter->name();
                }
            }
        }

        return $seenParameters;
    }
}
