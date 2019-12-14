<?php

namespace Phpactor\CodeBuilder\Util;

class TextFormat
{
    /**
     * @var string
     */
    private $indentation;

    public function __construct(string $indentation = '    ')
    {
        $this->indentation = $indentation;
    }

    public function indent(string $string, int $level = 0)
    {
        $lines = explode(PHP_EOL, $string);
        $lines = array_map(function ($line) use ($level) {
            return str_repeat($this->indentation, $level) . $line;
        }, $lines);

        return implode(PHP_EOL, $lines);
    }

    public static function indentationRemove(string $text): string
    {
        $text = preg_replace("/\n\s+/m", "\n", $text);
        $text = preg_replace("/^\s+/m", "", $text);

        return $text;
    }
}
