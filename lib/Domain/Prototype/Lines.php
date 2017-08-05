<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Lines extends Collection
{
    public static function fromLines(array $lines)
    {
        return new self($lines);
    }

    public function __toString()
    {
        return implode(PHP_EOL, $this->items);
    }

    protected function singularName(): string
    {
        return 'line';
    }
}
