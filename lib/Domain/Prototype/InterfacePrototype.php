<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;


final class InterfacePrototype extends Prototype
{
    /**
     * @var ExtendsInterfaces
     */
    private $extendsInterfaces;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Methods
     */
    private $methods;

    public function __construct(
        string $name,
        Methods $methods = null,
        ExtendsInterfaces $extendsInterfaces = null
    ) {
        $this->name = $name;
        $this->methods = $methods ?: Methods::empty();
        $this->extendsInterfaces = $extendsInterfaces ?: ExtendsInterfaces::empty();
    }

    public function name()
    {
        return $this->name;
    }

    public function methods(): Methods
    {
        return $this->methods;
    }

    public function extendsInterfaces(): ExtendsInterfaces
    {
        return $this->extendsInterfaces;
    }
}
