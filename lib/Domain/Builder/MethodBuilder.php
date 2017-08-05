<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

class MethodBuilder
{
    public function __construct(ClassBuilder $classBuilder, MethodHeaderBuilder $parent)
    {
        $this->parent = $parent;
        $this->name = $name;
    }
}
