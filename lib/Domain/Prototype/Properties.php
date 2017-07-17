<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Properties extends Collection
{
    public static function fromProperties(array $properties)
    {
        return new self($properties);
    }

    protected function singularName(): string
    {
        return 'property';
    }
}
