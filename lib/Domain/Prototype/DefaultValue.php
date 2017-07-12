<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class DefaultValue
{
    private $value;
    private $none = false;

    private function __construct($value = null)
    {
        $this->value = $value;
    }

    public static function fromValue($value)
    {
        return new self($value);
    }

    public static function none()
    {
        $new = new self();
        $new->none = true;

        return $new;
    }

    public static function null(): DefaultValue
    {
        return new self(null);
    }

    public function notNone()
    {
        return false === $this->none;
    }

    public function export()
    {
        if ($this->value === null) {
            return 'null';
        }

        return var_export($this->value, true);
    }
}

