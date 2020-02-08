<?php

namespace Phpactor\CodeBuilder\Util;

use Phpactor\CodeBuilder\Domain\TextEdits;

class TextEditRenderer
{
    public static function render(string $code, TextEdits ...$textEditss): string
    {
        $chars = mb_str_split($code);

        $offset = 0;
        $rows = [];
        $buffer = [];
        $maxCols = 0;
        foreach ($chars as $char) {
            $charCell = [
                'char' => $char,
                'edits' => [],
            ];
            foreach ($textEditss as $index => $textEdits) {
                foreach ($textEdits->atOffset($offset) as $edit) {
                    $charCell['edits'][] = $index;
                }
            }
            $buffer[] = $charCell;

            if ($char === PHP_EOL) {
                if (count($charCell) > $maxCols) {
                    $maxCols = count($charCell);
                }
                $rows = array_merge($rows, $buffer);
                $buffer = [];
            }
        }

        if ($buffer) {
            $rows = array_merge($rows, $buffer);
        }

        $out = [
            '<html>',
            '<table>'
        ];

        foreach ($rows as $row) {
            $out[] = '<tr>';
            for ($i = 0; $i < $maxCols; $i++) {
                $out[] = '<td>';
                if (isset($row[$i])) {
                    $cellData = $row[$i];
                    $out[] = '<td>';
                    $out[] = $cellData['char'];
                    $out[] = '</td>';
                }
                $out[] = '</td>';
            }
            $out[] = '</tr>';
        }
        $out[] = '</table>';
        $out[] = '</html>';

        return implode("\n", $out);
    }
}
