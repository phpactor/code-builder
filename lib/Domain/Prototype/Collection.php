<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Collection implements \IteratorAggregate
{
    private $items = [];

    public static function create()
    {
        return new static();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}

