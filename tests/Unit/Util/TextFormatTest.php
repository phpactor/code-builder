<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Util\TextFormat;

class TextFormatTest extends TestCase
{
    public function testThatItDoesNotIndentBlankLines()
    {
        $input = <<<EOT
Non blank line

Non blank line
EOT;
        $expectedOutput = <<<EOT
    Non blank line

    Non blank line
EOT;

        $formater = new TextFormat('    ');
        $this->assertSame($expectedOutput, $formater->indent($input, 1));
    }

    public function testThatItIndentOnMoreThanOneLevel()
    {
        $input = <<<EOT
Non blank line

Non blank line
EOT;
        $expectedOutput = <<<EOT
        Non blank line

        Non blank line
EOT;

        $formater = new TextFormat('    ');
        $this->assertSame($expectedOutput, $formater->indent($input, 2));
    }
}
