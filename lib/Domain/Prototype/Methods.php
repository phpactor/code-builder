<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Methods extends Collection
{
    public static function fromMethods(array $methods)
    {
        return new static(array_reduce($methods, function ($acc, Method $method) {
            $acc[$method->name()] = $method;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'method';
    }
}
