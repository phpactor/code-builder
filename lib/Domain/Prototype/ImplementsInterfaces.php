<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class ImplementsInterfaces extends Collection
{
    public static function fromTypes(array $types)
    {
        return new self($types);
    }
}
