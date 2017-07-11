<?php

namespace Phpactor\CodeBuilder\Domain\Collection;

abstract class AbstractCollection implements \IteratorAggregate
{
    private $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }
}
