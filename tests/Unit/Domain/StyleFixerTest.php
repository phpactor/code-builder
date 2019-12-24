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
        self::assertEquals(self::EXAMPLE_TEXT, $this->create()->fixIntersection(self::EXAMPLE_TEXT, TextEdits::none()));
    }

    public function testAppliesProposedChangesToTextRangesFromGivenTextEdits()
    {
        $proposer = $this->prophesize(StyleProposer::class);
        $proposer->propose(self::EXAMPLE_TEXT)->willReturn(TextEdits::fromTextEdits([
            new TextEdit(0, 1, 'b')
        ]));

        self::assertEquals('boo', $this->create([
            $proposer->reveal()
        ])->fixIntersection(self::EXAMPLE_TEXT, TextEdits::fromTextEdits([
            new TextEdit(0, 10, '0123456789')
        ])));
    }

    public function testTakesIntoAccountPreviouslyAppliedFixes()
    {
        $proposer1 = $this->prophesize(StyleProposer::class);
        $proposer1->propose('x x x x x')->willReturn(TextEdits::fromTextEdits([
            new TextEdit(0, 2, '')
        ]));
        $proposer2 = $this->prophesize(StyleProposer::class);
        $proposer2->propose('x x x x')->willReturn(TextEdits::fromTextEdits([
            new TextEdit(0, 2, '')
        ]));

        self::assertEquals('x x x', $this->create([
            $proposer1->reveal(),
            $proposer2->reveal(),
        ])->fixIntersection('x x x x x', TextEdits::fromTextEdits([
            new TextEdit(0, 0, 'x'),
            new TextEdit(0, 0, 'x '),
            new TextEdit(0, 0, 'x '),
            new TextEdit(0, 0, 'x '),
            new TextEdit(0, 0, 'x '),
        ])));
    }

    public function testTakesIntoAccountPreviouslyAppliedFixes2()
    {
        $proposer1 = $this->prophesize(StyleProposer::class);
        $proposer1->propose('x x x x ')->willReturn(TextEdits::fromTextEdits([
            new TextEdit(0, 0, 'y y ')
        ]));
        $proposer2 = $this->prophesize(StyleProposer::class);
        $proposer2->propose('y y x x x x ')->willReturn(TextEdits::fromTextEdits([
            new TextEdit(4, 2, '')
        ]));

        self::assertEquals('y y x x x x ', $this->create([
            $proposer1->reveal(),
            $proposer2->reveal(),
        ])->fixIntersection('x x x x ', TextEdits::fromTextEdits([
            new TextEdit(0, 0, 'x '),
            new TextEdit(2, 0, 'x '),
        ])));
    }

    private function create(array $proposers = []): StyleFixer
    {
        return new StyleFixer(...$proposers);
    }
}
