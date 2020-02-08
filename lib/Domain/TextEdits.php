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
     * Apply this set of edits to the given text
     */
    public function apply(string $text): string
    {
        $textEdits = $this->textEdits;

        return TextEdit::applyEdits($textEdits, $text);
    }

    /**
     * Return a set of text edits representing these text edits as if they had
     * been applied.
     *
     * If you applied the returned set of edits on the same text this set was
     * applied to, the returned set would make no further modifications.
     */
    public function appliedTextEdits(): self
    {
        $applied = [];
        $delta = 0;

        foreach ($this->textEdits as $textEdit) {
            $applied[] = new TextEdit(
                $textEdit->start + $delta,
                strlen($textEdit->content),
                $textEdit->content
            );
            $delta = strlen($textEdit->content) - $textEdit->length;
        }

        return self::fromTextEdits($applied);
    }

    public function integrate(TextEdits $newEdits): self
    {
        $integrated = iterator_to_array($newEdits);
        $deltas = [];

        $delta = 0;
        foreach ($this->textEdits as $myEdit) {
            assert($myEdit instanceof TextEdit);

            $integrated[] = new TextEdit(
                $myEdit->start + $newEdits->cumulativeDeltaUntil($myEdit->start),
                $myEdit->length,
                str_repeat('x', $myEdit->length)
            );
        }

        return TextEdits::fromTextEdits($integrated);
    }

    /**
     * Return a new set of text edits containing all the edits from this
     * class which overlap (the start position is on or between any of the start/end
     * positions) with the given text edits.
     */
    public function intersection(TextEdits $intersectEdits, int $delta = 0): TextEdits
    {
        $intersection = [];
        foreach ($this->textEdits as $myEdit) {
            foreach ($intersectEdits as $intersectEdit) {
                $intersectStart = $intersectEdit->start - $delta;
                $intersectEnd = $intersectEdit->start + $intersectEdit->length + $delta;

                if (
                    ($myEdit->start >= $intersectStart && $myEdit->start <= $intersectEdit->end()) ||
                    ($myEdit->end() >= $intersectStart && $myEdit->end() <= $intersectEdit->end())
                ) {
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

    private function cumulativeDeltaUntil(int $offset): int
    {
        $delta = 0;
        foreach ($this->textEdits as $textEdit) {
            $delta += $textEdit->delta();
        }
        return $delta;
    }
}
