<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class UseFunctionStatement 
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

    public static function fromNameAndAlias(string $type, string $alias = null)
    {
        return new self(Type::fromString($type), $alias);
    }

    public static function fromName(string $type)
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

    public function functionName(): Type
    {
        return $this->className;
    }
}
