<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\CodeBuilder\Domain\Prototype\InterfacePrototype;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\ClassMethodUpdater;

class InterfaceUpdater
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
        $this->methodUpdater = new InterfaceMethodUpdater($renderer);
    }

    public function updateInterface(
        Edits $edits,
        InterfacePrototype $classPrototype,
        InterfaceDeclaration $classNode
    )
    {
        $this->methodUpdater->updateMethods($edits, $classPrototype, $classNode);
    }
}
