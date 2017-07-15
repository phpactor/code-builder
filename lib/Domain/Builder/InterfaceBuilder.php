<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\Properties;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\Methods;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;
use Phpactor\CodeBuilder\Domain\Builder\InterfaceBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsInterfaces;
use Phpactor\CodeBuilder\Domain\Prototype\InterfacePrototype;

class InterfaceBuilder extends ClassLikeBuilder
{
    /**
     * @var Type[]
     */
    private $extends = [];

    public function extends(string $class): InterfaceBuilder
    {
        $this->extends[] = Type::fromString($class);

        return $this;
    }

    public function build(): InterfacePrototype
    {
        return new InterfacePrototype(
            $this->name,
            Methods::fromMethods(array_map(function (MethodBuilder $builder) {
                return $builder->build();
            }, $this->methods)),
            ExtendsInterfaces::fromTypes($this->extends)
        );
    }
}
