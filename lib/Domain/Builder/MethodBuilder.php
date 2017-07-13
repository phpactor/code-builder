<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\ClassProtoreturnType;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\Properties;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Prototype\ReturnType;

class MethodBuilder
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
     * @var Visibility
     */
    private $visibility;

    /**
     * @var Type
     */
    private $returnType;

    public function __construct(ClassBuilder $parent, string $name)
    {
        $this->parent = $parent;
        $this->name = $name;
    }

    public function visibility(string $visibility): MethodBuilder
    {
        $this->visibility = Visibility::fromString($visibility);

        return $this;
    }

    public function returnType(string $returnType): MethodBuilder
    {
        $this->returnType = ReturnType::fromString($returnType);

        return $this;
    }

    public function parameter(string $name): ParameterBuilder
    {
        $this->parameters[] = $builder = new ParameterBuilder($this, $name);

        return $builder;
    }

    public function build()
    {
        return new Method(
            $this->name,
            $this->visibility,
            Parameters::empty(),
            $this->returnType
        );
    }

    public function end(): ClassBuilder
    {
        return $this->parent;
    }
}
