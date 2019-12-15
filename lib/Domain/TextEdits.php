<?php

namespace Phpactor\CodeBuilder\Domain;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

class TextEdits implements IteratorAggregate
{
    /**
     * @var TextEdit[]
     */
    private $textEdits;

    public function __construct(TextEdit ...$textEdits)
    {
        $this->textEdits = $textEdits;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->textEdits);
    }

    public static function none(): self
    {
        return new self();
    }

    public static function fromTextEdits(array $textEdits): self
    {
        return new self(...$textEdits);
    }

    public function merge(TextEdits $edits): self
    {
        return new self(...array_merge($this->textEdits, $edits->textEdits));
    }

    public function apply(string $text): string
    {
        return TextEdit::applyEdits($this->textEdits, $text);
    }
}
