<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class ClassPrototype extends ClassLikePrototype
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
     * @var ExtendsClasss
     */
    private $extendsclasss;

    /**
     * @var Constants
     */
    private $constants;

    public function __construct(
        string $name,
        Properties $properties = null,
        Constants $constants = null,
        Methods $methods = null,
        ExtendsClass $extendsClass = null,
        ImplementsInterfaces $implementsInterfaces = null
    ) {
        parent::__construct($name, $methods);
        $this->properties = $properties ?: Properties::empty();
        $this->extendsClass = $extendsClass ?: ExtendsClass::none();
        $this->implementsInterfaces = $implementsInterfaces ?: ImplementsInterfaces::empty();
        $this->constants = $constants ?: Constants::empty();
    }

    public function properties(): Properties
    {
        return $this->properties;
    }

    public function constants(): Constants
    {
        return $this->constants;
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
