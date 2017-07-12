<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class ClassParent
{
    private $class;

    public function __construct(Type $class)
    {
        $this->class = $class;
    }

    public static function fromString($string)
    {
        return new self(Type::fromString($string));
    }

    public function __toString()
    {
        return (string) $this->class;
    }

    public static function none()
    {
        return new self(Type::none());
    }
}
