<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Util\TextUtil;

class TextUtilTest extends TestCase
{
    /**
     * @dataProvider provideSplitLines
     */
    public function testSplitLines(string $text, array $expectedLines)
    {
        self::assertEquals($expectedLines, TextUtil::lines($text));
    }

    public function provideSplitLines()
    {
        yield 'empty' => [
            '',
            ['']
        ];

        yield 'two lines' => [
            "\n",
            ['', '']
        ];

        yield 'windows' => [
            "\r\n",
            ['', '']
        ];

        yield 'mac' => [
            "\r",
            ['', '']
        ];

        yield 'text' => [
            "one\ntwo",
            ['one', 'two']
        ];
    }

    /**
     * @dataProvider provideLineIndentation
     */
    public function testLineIndentation(string $line, string $expectedIndentation)
    {
        self::assertEquals($expectedIndentation, TextUtil::lineIndentation($line));
    }

    public function provideLineIndentation()
    {
        yield 'empty' => [
            '',
            ''
        ];

        yield 'none' => [
            'foobar',
            ''
        ];

        yield 'some' => [
            '  foobar',
            '  '
        ];

        yield 'some tabs' => [
            "\t\tfoobar",
            "\t\t"
        ];
    }

    /**
     * @dataProvider provideLastNewLineOffset
     */
    public function testLastNewLineOffset(string $line, int $expectedOffset)
    {
        self::assertEquals($expectedOffset, TextUtil::lastNewLineOffset($line));
    }

    public function provideLastNewLineOffset()
    {
        yield 'empty' => [
            '',
            0 
        ];

        yield 'none' => [
            "\n",
            '0'
        ];

        yield 'none' => [
            "  \n",
            2
        ];

        yield 'windows' => [
            "  \r\n",
            2
        ];

        yield 'mac' => [
            "  \r",
            2
        ];

        yield 'none' => [
            <<<'EOT'
     foobar
     barfoo

EOT
        ,
            23
        ];
    }
}
