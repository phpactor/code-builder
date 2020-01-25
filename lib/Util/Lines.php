<?php

namespace Phpactor\CodeBuilder\Util;

use ArrayIterator;
use IteratorAggregate;
use RuntimeException;

class Lines implements IteratorAggregate
{
    /**
     * @var array<Line>
     */
    private $lines;

    private function __construct(string $text)
    {
        $linesAndDelimiters = preg_split("{(\r\n|\n|\r)}", $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $position = 0;
        $startPosition = $position;
        $lines = [];

        while ($linesAndDelimiters) {
            $lineContent = array_shift($linesAndDelimiters);
            $delim = array_shift($linesAndDelimiters);
            $startPosition = $position;
            $endPosition = $startPosition + strlen($lineContent) + strlen($delim);

            $lines[] = new Line($startPosition, $endPosition, $lineContent, $delim);
            $position = $endPosition;
        }
        $this->lines = $lines;
    }

    public static function fromText(string $text, $newLineChar = "\n"): Lines
    {
        return new self($text);
    }

    public function lines(): array
    {
        return $this->lines;
    }

    public function line(int $lineNo): Line
    {
        if ($lineNo < 1) {
            throw new RuntimeException(sprintf(
                'Line number cannot be less than one, got "%s"',
                $lineNo
            ));
        }

        if (!isset($this->lines[$lineNo - 1])) {
            throw new RuntimeException(sprintf(
                'Line number "%s" is not existing, I have "%s" lines',
                $lineNo,
                    count($this->lines)
                ));
        }

        return $this->lines[$lineNo - 1];
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->lines);
    }
}
