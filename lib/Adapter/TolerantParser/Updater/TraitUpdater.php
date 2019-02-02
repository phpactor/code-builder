<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Domain\Prototype\TraitPrototype;

class TraitUpdater extends ClassLikeUpdater
{
    public function updateTrait(Edits $edits, TraitPrototype $classPrototype, TraitDeclaration $classNode)
    {
        if (false === $classPrototype->applyUpdate()) {
            return;
        }

        $this->updateProperties($edits, $classPrototype, $classNode);

        $this->methodUpdater->updateMethods($edits, $classPrototype, $classNode);
    }

    /**
     * @return \Microsoft\PhpParser\Node\TraitMembers
     */
    protected function members(Node $node): Node
    {
        return $node->traitMembers;
    }

    /**
     * @return Node[]
     */
    protected function memberDeclarations(Node $node): array
    {
        return $node->traitMembers->traitMemberDeclarations;
    }
}
