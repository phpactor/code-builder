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

    public function indent(string $string, int $level = 0): string
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

    public function indentRemove(string $text): string
    {
        return preg_replace("/^[ \t]+/m", "", $text);
    }

    public function implodeLines(array $newLines): string
    {
        return implode($this->newLineChar, $newLines);
    }

    public function indentReplace($text, int $level): string
    {
        return $this->indent($this->indentRemove($text), $level);
    }

    public function newLineChar(): string
    {
        return $this->newLineChar;
    }
}
