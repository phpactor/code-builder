<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\Properties;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\Methods;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;
use Phpactor\CodeBuilder\Domain\Builder\ClassLikeBuilder;
use Phpactor\CodeBuilder\Domain\Builder\Builder;
use Phpactor\CodeBuilder\Domain\Builder\Exception\InvalidBuilderException;

abstract class ClassLikeBuilder implements Builder
{
    /**
     * @var SourceCodeBuilder
     */
    private $parent;

    /**
     * @var MethodBuilder[]
     */
    protected $methods = [];

    /**
     * @var string
     */
    protected $name;

    public function __construct(SourceCodeBuilder $parent, string $name)
    {
        $this->parent = $parent;
        $this->name = $name;
    }

    public function add(Builder $builder)
    {
        if ($builder instanceof MethodBuilder) {
            $this->methods[$builder->builderName()] = $builder;
            return;
        }

        throw new InvalidBuilderException($this, $builder);
    }

    public function method(string $name): MethodBuilder
    {
        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        }

        $this->methods[$name] = $builder = new MethodBuilder($this, $name);

        return $builder;
    }

    public function end(): SourceCodeBuilder
    {
        return $this->parent;
    }
}
