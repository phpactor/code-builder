<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class UseStatement 
{
    /**
     * @var Type
     */
    private $className;

    /**
     * @var string
     */
    private $alias;

    public function __construct(Type $className, string $alias = null)
    {
        $this->className = $className;
        $this->alias = $alias;
    }

    public static function fromTypeAndAlias(string $type, string $alias = null)
    {
        return new self(Type::fromString($type), $alias);
    }

    public static function fromType(string $type)
    {
        return new self(Type::fromString($type));
    }

    public function __toString()
    {
        if ($this->alias) {
            return (string) $this->className . ' as ' . $this->alias;
        }

        return (string) $this->className;
    }

    public function hasAlias(): bool
    {
        return null !== $this->alias;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function className(): Type
    {
        return $this->className;
    }
}
