<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Prototype\Parameter;

class ParameterBuilder
{
    /**
     * @var SourceCodeBuilder
     */
    private $parent;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var DefaultValue
     */
    private $defaultValue;

    public function __construct(MethodBuilder $parent, string $name)
    {
        $this->parent = $parent;
        $this->name = $name;
    }

    public function type(string $type): ParameterBuilder
    {
        $this->type = Type::fromString($type);

        return $this;
    }

    public function defaultValue($value): ParameterBuilder
    {
        $this->defaultValue = DefaultValue::fromValue($value);

        return $this;
    }

    public function build()
    {
        return new Parameter(
            $this->name,
            $this->type,
            $this->defaultValue
        );
    }

    public function end(): MethodBuilder
    {
        return $this->parent;
    }
}
