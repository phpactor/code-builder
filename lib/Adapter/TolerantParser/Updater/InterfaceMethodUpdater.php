<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\ClassLike;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;

class InterfaceMethodUpdater extends AbstractMethodUpdater
{
    protected function memberDeclarations(ClassLike $classNode)
    {
        return $classNode->interfaceMembers->interfaceMemberDeclarations;
    }

    public function memberDeclarationsNode(ClassLike $classNode)
    {
        return $classNode->interfaceMembers;
    }

    public function renderMethod(Renderer $renderer, Method $method)
    {
        return $renderer->render($method) . ';';
    }
}
