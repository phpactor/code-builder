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

    public function __construct(
        string $name,
        Visibility $visibility = null,
        Parameters $parameters = null
    )
    {
        $this->name = $name;
        $this->visibility = $visibility ?: Visibility::public();
        $this->parameters = $parameters ?: Parameters::empty();
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
}


