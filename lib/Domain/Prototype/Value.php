<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\Value;

class Value
{
    protected $value;

    protected function __construct($value = null)
    {
        $this->value = $value;
    }

    public static function fromValue($value)
    {
        return new static($value);
    }

    public function export()
    {
        if ($this->value === null) {
            return 'null';
        }

        return var_export($this->value, true);
    }
}

