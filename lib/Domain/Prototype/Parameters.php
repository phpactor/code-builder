<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @method \Phpactor\CodeBuilder\Domain\Prototype\Property first()
 * @method \Phpactor\CodeBuilder\Domain\Prototype\Property get()
 */
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
