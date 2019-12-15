<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;

class TextEditsTest extends TestCase
{
    /**
     * @dataProvider provideReturnsIntersectionOfGivenTextEdits
     */
    public function testReturnsIntersectionOfGivenTextEdits(array $edits1, array $edits2, array $expectedEdits)
    {
        self::assertEquals(
            TextEdits::fromTextEdits($expectedEdits),
            TextEdits::fromTextEdits($edits1)->intersection(
                TextEdits::fromTextEdits($edits2)
            )
        );
    }

    public function provideReturnsIntersectionOfGivenTextEdits()
    {
        yield 'empty' => [
            [
            ],
            [
            ],
            [
            ],
        ];
    }
}
