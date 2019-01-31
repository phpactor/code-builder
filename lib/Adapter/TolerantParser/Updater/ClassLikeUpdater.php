<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\StatementNode;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Domain\Prototype\ClassLikePrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Renderer;

abstract class ClassLikeUpdater
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var MethodUpdater
     */
    protected $methodUpdater;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->methodUpdater = new ClassMethodUpdater($renderer);
    }

    protected function resolvePropertyName(Node $property)
    {
        if ($property instanceof Variable) {
            return $property->getName();
        }

        if ($property instanceof AssignmentExpression) {
            return $this->resolvePropertyName($property->leftOperand);
        }

        throw new \InvalidArgumentException(sprintf(
            'Do not know how to resolve property element of type "%s"',
            get_class($property)
        ));
    }

    protected function updateConstants(Edits $edits, ClassLikePrototype $classPrototype, StatementNode $classNode)
    {
        if (count($classPrototype->constants()) === 0) {
            return;
        }

        switch ($classNode->getNodeKindName()) {
            case 'ClassDeclaration':
                $lastConstant = $classNode->classMembers->openBrace;
                $memberDeclarations = $classNode->classMembers->classMemberDeclarations;
                break;
            case 'TraitDeclaration':
                $lastConstant = $classNode->traitMembers->openBrace;
                $memberDeclarations = $classNode->traitMembers->traitMemberDeclarations;
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'Do not know how to handle class node declaration type "%s"',
                    $classNode->getNodeKindName()
                ));
        }

        $nextMember = null;
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

    protected function updateProperties(Edits $edits, ClassLikePrototype $classPrototype, StatementNode $classNode)
    {
        if (count($classPrototype->properties()) === 0) {
            return;
        }

        switch ($classNode->getNodeKindName()) {
            case 'ClassDeclaration':
                $lastProperty = $classNode->classMembers->openBrace;
                $memberDeclarations = $classNode->classMembers->classMemberDeclarations;
                break;
            case 'TraitDeclaration':
                $lastProperty = $classNode->traitMembers->openBrace;
                $memberDeclarations = $classNode->traitMembers->traitMemberDeclarations;
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'Do not know how to handle class node declaration type "%s"',
                    $classNode->getNodeKindName()
                ));
        }

        $nextMember = null;
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
}
