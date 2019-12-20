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
        $theirEdits = $edits->textEdits;
        $edits = $this->textEdits;

        foreach ($theirEdits as $theirEdit) {
            $edits = $this->add($edits, $theirEdit);
        }

        return new self(...$edits);
    }

    public function apply(string $text): string
    {
        $textEdits = $this->textEdits;

        return TextEdit::applyEdits($textEdits, $text);
    }

    public function intersection(TextEdits $textEdits): TextEdits
    {
        $intersection = [];
        foreach ($this->textEdits as $textEdit1) {
            $start = $textEdit1->start;
            $end = $textEdit1->start + $textEdit1->length;

            foreach ($textEdits as $textEdit2) {
                if ($textEdit2->start >= $start && $textEdit2->start < $end) {
                    $intersection[] = $textEdit1;
                    continue 2;
                }
            }
        }

        return new self(...$intersection);
    }

    private function add(array $myEdits, TextEdit $theirEdit): array
    {
        $new = [];
        $inserted = false;

        foreach ($myEdits as $myEdit) {
            if (!$inserted && $theirEdit->start < $myEdit->start) {
                $new[] = $theirEdit;
                $inserted = true;
            }

            $new[] = $myEdit;
        }

        if (!$inserted) {
            $new[] = $theirEdit;
        }

        return $new;
    }
}
