<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Fixer;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Fixer\ChainFixer;
use Phpactor\CodeBuilder\Domain\StyleFixer;

class ChainFixerTest extends TestCase
{
    public function testEmptyFixer()
    {
        self::assertEquals('foobar', (new ChainFixer())->fix('foobar'));
    }

    public function testAppliesFixers()
    {
        $fixer = new class implements StyleFixer {
            public function fix(string $string): string 
            {
                return 'barfoo';
            }
        };

        self::assertEquals('barfoo', (new ChainFixer(
            $fixer
        ))->fix('foobar'));
    }
}
