<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Fixer;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Fixer\ChainFixer;
use Phpactor\CodeBuilder\Domain\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;

class ChainFixerTest extends TestCase
{
    public function testEmptyFixer()
    {
        self::assertEquals('foobar', (new ChainFixer())->propose('foobar')->apply('foobar'));
    }

    public function testAppliesFixers()
    {
        $fixer = new class implements StyleProposer {
            public function propose(string $string): TextEdits 
            {
                return TextEdits::fromTextEdits([new TextEdit(0, 0, 'hallo')]);
            }
        };

        self::assertEquals('hallofoobar', (new ChainFixer(
            $fixer
        ))->propose('foobar')->apply('foobar'));
    }
}
