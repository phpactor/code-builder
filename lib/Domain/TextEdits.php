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

    /**
     * Merge one set of edits into this set.
     *
     * Edits from this set are ordered before those of the merged edits.
     */
    public function merge(TextEdits $edits): self
    {
        return new self(...array_merge($this->textEdits, $edits->textEdits));
    }

    /**
     * Apply this set of tedits to the given text
     */
    public function apply(string $text): string
    {
        $textEdits = $this->textEdits;

        return TextEdit::applyEdits($textEdits, $text);
    }

    /**
     * Return a new set of text edits containing all the edits from this
     * class which overlap (the start position is on or between any of the start/end
     * positions) with the given text edits.
     */
    public function intersection(TextEdits $textEdits, int $delta = 0): TextEdits
    {
        $intersection = [];
        foreach ($this->textEdits as $myEdit) {
            $start = $myEdit->start - $delta;
            $end = $myEdit->start + $myEdit->length + $delta;

            foreach ($textEdits as $theirEdit) {
                if ($theirEdit->start >= $start && $theirEdit->start <= $end) {
                    $intersection[] = $myEdit;
                    continue 2;
                }
            }
        }

        return new self(...$intersection);
    }

    public function add(TextEdit $textEdit): self
    {
        return new self(...array_merge($this->textEdits, [$textEdit]));
    }

    public static function one(TextEdit $textEdit)
    {
        return new self($textEdit);
    }
}
