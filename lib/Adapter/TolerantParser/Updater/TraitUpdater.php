<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Phpactor\CodeBuilder\Domain\Prototype\ClassLikePrototype;
use Phpactor\CodeBuilder\Domain\Prototype\TraitPrototype;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\StatementNode;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node;
use Phpactor\CodeBuilder\Domain\Prototype\Type;

class TraitUpdater extends ClassLikeUpdater
{
    public function updateTrait(Edits $edits, TraitPrototype $classPrototype, TraitDeclaration $classNode)
    {
        if (false === $classPrototype->applyUpdate()) {
            return;
        }

        $this->updateConstants($edits, $classPrototype, $classNode);
        $this->updateProperties($edits, $classPrototype, $classNode);

        $this->methodUpdater->updateMethods($edits, $classPrototype, $classNode);
    }

    private function updateProperties(Edits $edits, TraitPrototype $classPrototype, TraitDeclaration $classNode)
    {
        if (count($classPrototype->properties()) === 0) {
            return;
        }

        $lastProperty = $classNode->traitMembers->openBrace;
        $nextMember = null;

        $memberDeclarations = $classNode->traitMembers->traitMemberDeclarations;
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
