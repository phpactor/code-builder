<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Method extends Prototype
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var ReturnType
     */
    private $returnType;

    /*
     * @var Docblock
     */
    private $docblock;

    public function __construct(
        string $name,
        Visibility $visibility = null,
        Parameters $parameters = null,
        ReturnType $returnType = null,
        Docblock $docblock = null
    )
    {
        $this->name = $name;
        $this->visibility = $visibility ?: Visibility::public();
        $this->parameters = $parameters ?: Parameters::empty();
        $this->returnType = $returnType ?: ReturnType::none();
        $this->docblock = $docblock ?: Docblock::none();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function visibility(): Visibility
    {
        return $this->visibility;
    }

    public function parameters(): Parameters
    {
        return $this->parameters;
    }

    public function returnType(): ReturnType
    {
        return $this->returnType;
    }

    public function docblock(): Docblock
    {
        return $this->docblock;
    }
}
