<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Constant;
use Phpactor\CodeBuilder\Domain\Prototype\Value;
use Phpactor\CodeBuilder\Domain\Builder\NamedBuilder;

class ConstantBuilder implements NamedBuilder
{
    /**
     * @var ClassBuilder
     */
    private $parent;

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    public function __construct(ClassBuilder $parent, string $name, $value)
    {
        $this->parent = $parent;
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
