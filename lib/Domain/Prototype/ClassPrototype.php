<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\Methods;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;

final class ClassPrototype extends Prototype
{

    /**
     * @var ExtendsClass
     */
    private $extendsClass;

    /**
     * @var ImplementsInterfaces
     */
    private $implementsInterfaces;

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

    /**
     * @var ExtendsClasss
     */
    private $extendsclasss;

    public function __construct(
        string $name,
        Properties $properties = null,
        Methods $methods = null,
        ExtendsClass $extendsClass = null,
        ImplementsInterfaces $implementsInterfaces = null
    )
    {
        $this->name = $name;
        $this->properties = $properties ?: Properties::empty();
        $this->methods = $methods ?: Methods::empty();
        $this->extendsClass = $extendsClass ?: ExtendsClass::none();
        $this->implementsInterfaces = $implementsInterfaces ?: ImplementsInterfaces::empty();
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

    public function extendsClass(): ExtendsClass
    {
        return $this->extendsClass;
    }

    public function implementsInterfaces(): ImplementsInterfaces
    {
        return $this->implementsInterfaces;
    }
}
