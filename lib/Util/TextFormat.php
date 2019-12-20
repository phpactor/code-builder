<?php

namespace Phpactor\CodeBuilder\Util;

class TextFormat
{
    /**
     * @var string
     */
    private $indentation;

    /**
     * @var string
     */
    private $newLineChar;

    public function __construct(string $indentation = '    ', string $newLineChar = "\n")
    {
        $this->indentation = $indentation;
        $this->newLineChar = $newLineChar;
    }

    public function indent(string $string, int $level = 0)
    {
        $lines = TextUtil::lines($string);
        $lines = array_map(function ($line) use ($level) {
            if (!$line) {
                return $line;
            }
            return str_repeat($this->indentation, $level) . $line;
        }, $lines);

        return implode($this->newLineChar, $lines);
    }

    public function indentationRemove(string $text): string
    {
        $text = preg_replace("/^ +/m", "", $text);

        return $text;
    }

    public static function indentApply(string $text, string $indentation, int $level)
    {
        return (new self($indentation))->indent($text, $level);
    }

    public function implodeLines(array $newLines): string
    {
        return implode($this->newLineChar, $newLines);
    }

    public function newLineChar(): string
    {
        return $this->newLineChar;
    }
}
