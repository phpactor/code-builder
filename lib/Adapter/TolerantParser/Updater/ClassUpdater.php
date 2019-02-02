<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;

class ClassUpdater extends ClassLikeUpdater
{
    public function updateClass(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (false === $classPrototype->applyUpdate()) {
            return;
        }

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

    protected function updateConstants(Edits $edits, ClassPrototype $classPrototype, ClassDeclaration $classNode)
    {
        if (count($classPrototype->constants()) === 0) {
            return;
        }

        $lastConstant = $classNode->classMembers->openBrace;
        $memberDeclarations = $classNode->classMembers->classMemberDeclarations;

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

    /**
     * @return Microsoft\PhpParser\Node\ClassMembersNode
     */
    protected function members(Node $node): Node
    {
        return $node->classMembers;
    }

    /**
     * @return Node[]
     */
    protected function memberDeclarations(Node $node): array
    {
        return $node->classMembers->classMemberDeclarations;
    }
}
