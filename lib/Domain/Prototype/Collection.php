<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Collection implements \IteratorAggregate, \Countable
{
    protected $items = [];

    protected function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function empty()
    {
        return new static([]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Return first
     *
     * @return static
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->items);
    }
}
