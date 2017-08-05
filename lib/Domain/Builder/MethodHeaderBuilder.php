<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\ClassProtoreturnType;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\Properties;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Builder\MethodHeaderBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Domain\Prototype\MethodHeader;
use Phpactor\CodeBuilder\Domain\Prototype\ReturnType;
use Phpactor\CodeBuilder\Domain\Prototype\Docblock;

class MethodHeaderBuilder
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

    /**
     * @var ParameterBuilder[]
     */
    private $parameters = [];

    /**
     * @var Docblock
     */
    private $docblock;

    /**
     * @var bool
     */
    private $static = false;

    /**
     * @var bool
     */
    private $abstract = false;

    public function __construct(ClassLikeBuilder $parent, string $name)
    {
        $this->parent = $parent;
        $this->name = $name;
    }

    public function visibility(string $visibility): MethodHeaderBuilder
    {
        $this->visibility = Visibility::fromString($visibility);

        return $this;
    }

    public function returnType(string $returnType): MethodHeaderBuilder
    {
        $this->returnType = ReturnType::fromString($returnType);

        return $this;
    }

    public function parameter(string $name): ParameterBuilder
    {
        $this->parameters[] = $builder = new ParameterBuilder($this, $name);

        return $builder;
    }

    public function docblock(string $docblock): MethodHeaderBuilder
    {
        $this->docblock = Docblock::fromString($docblock);

        return $this;
    }

    public function build()
    {
        $modifiers = 0;

        if ($this->static) {
            $modifiers = $modifiers|MethodHeader::IS_STATIC;
        }

        if ($this->abstract) {
            $modifiers = $modifiers|MethodHeader::IS_ABSTRACT;
        }

        return new MethodHeader(
            $this->name,
            $this->visibility,
            Parameters::fromParameters(array_map(function (ParameterBuilder $builder) {
                return $builder->build();
            }, $this->parameters)),
            $this->returnType,
            $this->docblock,
            $modifiers
        );
    }

    public function static(): MethodHeaderBuilder
    {
        $this->static = true;
        return $this;
    }

    public function abstract(): MethodHeaderBuilder
    {
        $this->abstract = true;
        return $this;
    }

    public function end(): ClassLikeBuilder
    {
        return $this->parent;
    }
}
