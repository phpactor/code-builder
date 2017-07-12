<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;

final class Parameter extends Prototype
{
    private $name;
    private $type;
    private $defaultValue;

    public function __construct(
        string $name,
        Type $type = null,
        DefaultValue $defaultValue = null
    )
    {
        $this->name = $name;
        $this->type = $type ?: Type::none();
        $this->defaultValue = $defaultValue ?: DefaultValue::none();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function defaultValue(): DefaultValue
    {
        return $this->defaultValue;
    }
}
