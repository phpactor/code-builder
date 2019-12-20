<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\CodeBuilder\Domain\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;

class StyleFixerTest extends TestCase
{
    const EXAMPLE_TEXT = 'foo';

    public function testAppliesNoChangesWithNoProposers()
    {
        self::assertEquals(self::EXAMPLE_TEXT, $this->create()->fix(self::EXAMPLE_TEXT, TextEdits::none()));
    }

    public function testAppliesProposedChangesToTextRangesFromGivenTextEdits()
    {
        $proposer = $this->prophesize(StyleProposer::class);
        $proposer->propose(self::EXAMPLE_TEXT)->willReturn(TextEdits::fromTextEdits([
            new TextEdit(0, 1, 'b')
        ]));

        self::assertEquals('boo', $this->create([
            $proposer->reveal()
        ])->fix(self::EXAMPLE_TEXT, TextEdits::fromTextEdits([
            new TextEdit(0, 10, '0123456789')
        ])));
    }

    private function create(array $proposers = []): StyleFixer
    {
        return new StyleFixer(...$proposers);
    }
}
