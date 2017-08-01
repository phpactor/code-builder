<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class MethodBody extends Prototype implements \IteratorAggregate
{
    /**
     * @var Lines
     */
    private $lines;

    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->lines);
    }
}

