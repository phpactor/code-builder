<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Classes extends Collection
{
    public static function fromClasses(array $classes)
    {
        return new static(array_reduce($classes, function ($acc, $class) {
            $acc[$class->name()] = $class;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'class';
    }

    public function notIn(array $names): Classes
    {
        return new static(array_filter($this->items, function (ClassPrototype $prototype) use ($names) {
            return false === in_array($prototype->name(), $names);
        }));
    }

    public function in(array $names): Classes
    {
        return new static(array_filter($this->items, function (ClassPrototype $prototype) use ($names) {
            return true === in_array($prototype->name(), $names);
        }));
    }
}
