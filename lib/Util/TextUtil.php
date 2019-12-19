<?php

namespace Phpactor\CodeBuilder\Util;

class TextUtil
{
    public static function lines(string $text): array
    {
        return preg_split("{(\r\n|\n|\r)}", $text);
    }

    public static function lineIndentation($line): string
    {
        if (!preg_match('{^([\t ]*)}', $line, $matches)) {
            return '';
        }

        return $matches[1];
    }
}
