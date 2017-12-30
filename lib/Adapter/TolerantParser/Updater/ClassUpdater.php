<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;
use Phpactor\CodeBuilder\Domain\Renderer;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\ClassMethodUpdater;

class ClassUpdater
{
    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var MethodUpdater
     */
    private $methodUpdater;


    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->methodUpdater = new ClassMethodUpdater($renderer);
    }

    public function updateClass(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        $this->updateExtends($edits, $classPrototype, $classNode);
        $this->updateImplements($edits, $classPrototype, $classNode);
        $this->updateConstants($edits, $classPrototype, $classNode);
        $this->updateProperties($edits, $classPrototype, $classNode);

        $this->methodUpdater->updateMethods($edits, $classPrototype, $classNode);
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
                PHP_EOL . $edits->indent($this->renderer->render($constant), 1)
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
            // if property type exists then the last property has a docblock - add a line break
            if ($lastProperty instanceof PropertyDeclaration && $property->type() != Type::none()) {
                $edits->after($lastProperty, PHP_EOL);
            }

            $edits->after(
                $lastProperty,
                PHP_EOL . $edits->indent($this->renderer->render($property), 1)
            );

            if ($classPrototype->properties()->isLast($property) && $nextMember instanceof MethodDeclaration) {
                $edits->after($lastProperty, PHP_EOL);
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
}
