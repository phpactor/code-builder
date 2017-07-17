<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Parameters extends Collection
{
    public static function fromParameters(array $parameters)
    {
        return new self($parameters);
    }

    protected function singularName(): string
    {
        return 'parameter';
    }
}
