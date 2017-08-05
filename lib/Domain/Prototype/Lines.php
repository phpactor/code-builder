<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Lines extends Collection
{
    public static function fromLines(array $parameters)
    {
        return new self($parameters);
    }

    protected function singularName(): string
    {
        return 'line';
    }
}
