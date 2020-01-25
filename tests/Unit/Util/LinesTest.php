<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Util\Line;
use Phpactor\CodeBuilder\Util\Lines;

class LinesTest extends TestCase
{
    public function testCreateLines()
    {
        $text = <<<'EOT'
Hello
Goodbye
Ciao
EOT;
        $lines = Lines::fromText($text);

        $this->assertEquals(new Line(0, 6, 'Hello', "\n"), $lines->line(1));
        $this->assertEquals(new Line(6, 14, 'Goodbye', "\n"), $lines->line(2));
    }
}
