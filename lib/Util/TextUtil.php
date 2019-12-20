<?php

namespace Phpactor\CodeBuilder\Util;

class TextUtil
{
    const NL_WINDOWS = "\r\n";
    const NL_MAC = "\r";
    const NL_UNIX = "\n";


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
        if (false !== $pos = strrpos($text, self::NL_WINDOWS)) {
            return $pos;
        }

        if (false !== $pos = strrpos($text, self::NL_MAC)) {
            return $pos;
        }

        if (false !== $pos = strrpos($text, self::NL_UNIX)) {
            return $pos;
        }

        return 0;
    }
}
