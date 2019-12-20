<?php

namespace Phpactor\CodeBuilder\Util;

class TextUtil
{
    public static function lines(string $text): array
    {
        return preg_split("{(\r\n|\n|\r)}", $text);
    }

    public static function lineIndentation(string $line): string
    {
        if (!preg_match('{^([\t ]*)}', $line, $matches)) {
            return '';
        }

        return $matches[1];
    }

    public static function lastNewLineOffset(string $text): int
    {
        if (false !== $pos = strrpos($text, "\r\n")) {
            return $pos;
        }

        if (false !== $pos = strrpos($text, "\r")) {
            return $pos;
        }

        if (false !== $pos = strrpos($text, "\n")) {
            return $pos;
        }

        return 0;
    }
}
