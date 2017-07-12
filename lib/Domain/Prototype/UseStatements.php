<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class UseStatements extends Collection
{
    public static function fromQualifiedNames(array $names)
    {
        return new self($names);
    }
}
