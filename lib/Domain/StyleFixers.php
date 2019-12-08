<?php

namespace Phpactor\CodeBuilder\Domain;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

class StyleFixers implements IteratorAggregate
{
    private $fixers;

    public function __construct(array $fixers)
    {
        foreach ($fixers as $fixer) {
            $this->add($fixer);
        }
    }

    private function add(StyleFixer $fixer): void
    {
        $this->fixers[] = $fixer;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->fixers);
    }
}
