<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Classes extends Collection
{
    public static function fromClasses(array $classes)
    {
        return new static($classes);
    }
}
