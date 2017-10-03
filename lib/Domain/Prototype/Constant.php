<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Prototype\Value;

class Constant extends Prototype
{
    /**
     * @var string
     */
    private $name;

    private $value;

    public function __construct(string $name, Value $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): Value
    {
        return $this->value;
    }
}

