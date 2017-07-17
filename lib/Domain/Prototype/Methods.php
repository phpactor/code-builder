<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Methods extends Collection
{
    public static function fromMethods(array $methods)
    {
        return new self($methods);
    }

    protected function singularName(): string
    {
        return 'method';
    }
}
