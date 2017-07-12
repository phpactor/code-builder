<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;

final class Property extends Prototype
{
    private $name;
    private $visibility;

    public function __construct(
        string $name,
        Visibility $visibility = null,
        DefaultValue $defaultValue = null
    )
    {
        $this->name = $name;
        $this->visibility = $visibility ?: Visibility::public();
        $this->defaultValue = $defaultValue ?: DefaultValue::none();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function visibility(): Visibility
    {
        return $this->visibility;
    }
}
