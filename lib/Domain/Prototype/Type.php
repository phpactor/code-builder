<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Type\Exception\TypeCannotBeNullableException;

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

    /**
     * @var bool
     */
    private $nullable = false;

    public function __construct(string $type = null, bool $nullable = false)
    {
        parent::__construct();
        $this->type = $type;
        $this->nullable = $nullable;
    }

    public static function fromString(string $string): Type
    {
        $nullable = 0 === strpos($string, '?');
        $type = $nullable ? substr($string, 1) : $string;

        return new self($type, $nullable);
    }

    public static function none(): Type
    {
        $new = new self();
        $new->none = true;

        return $new;
    }

    public function __toString()
    {
        return $this->nullable ? sprintf('?%s', $this->type) : $this->type;
    }

    public function namespace(): ?string
    {
        if (null === $this->type) {
            return null;
        }

        if (false === strpos($this->type, '\\')) {
            return null;
        }

        return substr($this->type, 0, strrpos($this->type, '\\'));
    }

    public function notNone(): bool
    {
        return false === $this->none;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }
}
