<?php

namespace Phpactor\CodeBuilder\Util;

final class TextUtil
{
    private const NL_WINDOWS = "\r\n";
    private const NL_MAC = "\r";
    private const NL_UNIX = "\n";

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

    public static function leadingSpace(string $text): string
    {
        if (!preg_match("{^(\s*)}m", $text, $matches)) {
            return 0;
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

    public static function hasDocblock(string $line): bool
    {
        return (bool)preg_match('{^\s*\*}m', $line);
    }
}
