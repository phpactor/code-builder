<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\Properties;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\Methods;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;

class ClassBuilder extends ClassLikeBuilder
{
    /**
     * @var string
     */
    private $extends;

    /**
     * @var PropertyBuilder[]
     */
    private $properties = [];

    /**
     * @var Type[]
     */
    private $interfaces = [];

    public function extends(string $class): ClassBuilder
    {
        $this->extends = ExtendsClass::fromString($class);

        return $this;
    }

    public function implements(string $interface): ClassBuilder
    {
        $this->interfaces[] = Type::fromString($interface);

        return $this;
    }

    public function property(string $name): PropertyBuilder
    {
        $this->properties[] = $builder = new PropertyBuilder($this, $name);

        return $builder;
    }

    public function build(): ClassPrototype
    {
        return new ClassPrototype(
            $this->name,
            Properties::fromProperties(array_map(function (PropertyBuilder $builder) {
                return $builder->build();
            }, $this->properties)),
            Methods::fromMethods(array_map(function (MethodBuilder $builder) {
                return $builder->build();
            }, $this->methods)),
            $this->extends,
            ImplementsInterfaces::fromTypes($this->interfaces)
        );
    }
}
