<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Type extends Prototype
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $none = false;

    public function __construct(string $type = null)
    {
        $this->type = $type;
    }

    public static function fromString(string $string): Type
    {
        return new self($string);
    }

    public static function none(): Type
    {
        $new = new self();
        $new->none = true;

        return $new;
    }

    public function __toString()
    {
        return $this->type;
    }

    public function namespace(): ?string
    {
        if (null === $this->type) {
            return null;
        }

        if (false === strpos($this->type, '\\')) {
            return null;
        }

        return substr($this->type, 0, strpos($this->type, '\\'));
    }

    public function notNone(): bool
    {
        return false === $this->none;
    }
}
