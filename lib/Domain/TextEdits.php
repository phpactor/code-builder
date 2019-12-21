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
        usort($textEdits, function (TextEdit $a, TextEdit $b) {
            return $a->start <=> $b->start;
        });
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
        $textEdits = $this->textEdits;

        return TextEdit::applyEdits($textEdits, $text);
    }

    public function intersection(TextEdits $textEdits): TextEdits
    {
        $intersection = [];
        foreach ($this->textEdits as $myEdit) {
            $start = $myEdit->start;
            $end = $myEdit->start + $myEdit->length;

            foreach ($textEdits as $theirEdit) {
                if ($theirEdit->start >= $start && $theirEdit->start <= $end) {
                    $intersection[] = $myEdit;
                    continue 2;
                }
            }
        }

        return new self(...$intersection);
    }
}
