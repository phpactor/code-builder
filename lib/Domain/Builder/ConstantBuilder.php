<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Constant;
use Phpactor\CodeBuilder\Domain\Prototype\Value;

class ConstantBuilder extends AbstractBuilder implements NamedBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = Value::fromValue($value);
    }

    public function build(): Constant
    {
        return new Constant(
            $this->name,
            $this->value
        );
    }

    public function end(): ClassBuilder
    {
        return $this->parent;
    }

    public function builderName(): string
    {
        return $this->name;
    }
}
