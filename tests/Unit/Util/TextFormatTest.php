<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Util\TextFormat;

class TextFormatTest extends TestCase
{
    /**
     * @dataProvider provideRemoveIndentation
     */
    public function testRemoveIndentation(string $text, string $expeced)
    {
        self::assertEquals($expeced, TextFormat::indentationRemove($text));
    }

    public function provideRemoveIndentation()
    {
        yield 'empty' => [
            '',
            ''
        ];

        yield 'uniform' => [
            <<<'EOT'
  asd
  asd
  asd
EOT
           ,
            <<<'EOT'
asd
asd
asd
EOT
        ];

        yield 'different indentations' => [
            <<<'EOT'
  asd
    asd
  asd
EOT
           ,
            <<<'EOT'
asd
asd
asd
EOT
        ];

        yield 'code' => [
            <<<'EOT'
class Foo
{
    public function bar()
    {
        echo $hello;
    }
}
EOT
           ,
            <<<'EOT'
class Foo
{
public function bar()
{
echo $hello;
}
}
EOT
        ];

        yield 'preserve new line' => [
            <<<'EOT'

class Foo
{
    public function bar()
    {
        echo $hello;
    }
}
EOT
           ,
           <<<'EOT'

class Foo
{
public function bar()
{
echo $hello;
}
}
EOT
        ];
    }

    /**
     * @dataProvider provideIndent
     */
    public function testIndentat(string $text, int $level, string $expected)
    {
        self::assertEquals($expected, TextFormat::indentApply($text, '    ', $level));
    }

    public function provideIndent()
    {
        yield 'empty' => [
            '',
            0,
            ''
        ];

        yield 'exmaple 1' => [
            <<<'EOT'
private $bar;
EOT
            ,
            1,
           <<<'EOT'
    private $bar;
EOT
        ];

        yield 'exmaple 2' => [
            <<<'EOT'
private $bar;

private $bar;
EOT
            ,
            1,
           <<<'EOT'
    private $bar;

    private $bar;
EOT
        ];
    }
}
