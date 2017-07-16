<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Classes extends Collection
{
    public static function fromClasses(array $classes)
    {
        return new static($classes);
    }

    public function notIn(array $names): Classes
    {
        return new static(array_filter($this->items, function (ClassPrototype $prototype) use ($names) {
            return false === in_array($prototype->name(), $names);
        }));
    }
}
