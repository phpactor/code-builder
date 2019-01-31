<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

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

        $this->updateConstants($edits, $classPrototype, $classNode);
        $this->updateProperties($edits, $classPrototype, $classNode);

        $this->methodUpdater->updateMethods($edits, $classPrototype, $classNode);
    }
}
