<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\Methods;

final class ClassPrototype extends Prototype
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Properties
     */
    private $properties;

    /**
     * @var Methods
     */
    private $methods;

    public function __construct(
        string $name,
        Properties $properties = null,
        Methods $methods = null,
        ClassParent $classParent = null
    )
    {
        $this->name = $name;
        $this->properties = $properties ?: Properties::empty();
        $this->methods = $methods ?: Methods::empty();
        $this->parentClass = $classParent ?: ClassParent::none();
    }

    public function name()
    {
        return $this->name;
    }

    public function properties(): Properties
    {
        return $this->properties;
    }

    public function methods(): Methods
    {
        return $this->methods;
    }
}

