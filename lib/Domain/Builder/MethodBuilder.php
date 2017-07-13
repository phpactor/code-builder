<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\ProtoreturnType\ClassProtoreturnType;
use Phpactor\CodeBuilder\Domain\ProtoreturnType\ExtendsClass;
use Phpactor\CodeBuilder\Domain\ProtoreturnType\Properties;
use Phpactor\CodeBuilder\Domain\ProtoreturnType\Type;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\ProtoreturnType\Visibility;
use Phpactor\CodeBuilder\Domain\ProtoreturnType\DefaultValue;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Domain\Prototype\Method;

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
        $this->returnType = Type::fromString($returnType);

        return $this;
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
