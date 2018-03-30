<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class UseStatements extends Collection
{
    protected function singularName(): string
    {
        return 'use statement';
    }

    public static function fromUseStatements(array $useStatements)
    {
        return new self($useStatements);
    }
}
