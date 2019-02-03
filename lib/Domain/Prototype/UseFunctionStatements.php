<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\UseFunctionStatements;

class UseFunctionStatements extends Collection
{
    protected function singularName(): string
    {
        return 'use function statement';
    }

    public static function fromUseFunctionStatements(array $useStatements)
    {
        return new self($useStatements);
    }
}
