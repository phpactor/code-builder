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

        yield 'no intersect' => [
            [
                new TextEdit(0, 5, 'foobar'),
            ],
            [
                new TextEdit(10, 5, 'foobar'),
            ],
            [
            ],
        ];

        yield 'no intersect 2' => [
            [
                new TextEdit(0, 5, 'foobar'),
            ],
            [
                new TextEdit(5, 5, 'foobar'),
            ],
            [
            ],
        ];

        yield 'intersect 1' => [
            [
                new TextEdit(0, 5, 'foobar'),
            ],
            [
                new TextEdit(0, 5, 'foobar'),
            ],
            [
                new TextEdit(0, 5, 'foobar'),
            ],
        ];

        yield 'intersect 2' => [
            [
                new TextEdit(0, 5, 'foobar'),
            ],
            [
                new TextEdit(4, 5, 'foobar'),
            ],
            [
                new TextEdit(0, 5, 'foobar'),
            ],
        ];

        yield 'intersect 3' => [
            [
                new TextEdit(0, 5, 'foobar'),
            ],
            [
                new TextEdit(4, 5, 'foobar'),
                new TextEdit(2, 5, 'foobar'),
                new TextEdit(10, 5, 'foobar'),
            ],
            [
                new TextEdit(0, 5, 'foobar'),
            ],
        ];

        yield 'intersect 4' => [
            [
                new TextEdit(0, 5, 'foobar'),
                new TextEdit(15, 1, 'foobar'),
            ],
            [
                new TextEdit(4, 5, 'foobar'),
                new TextEdit(15, 5, 'foobar'),
                new TextEdit(16, 5, 'foobar'),
            ],
            [
                new TextEdit(0, 5, 'foobar'),
                new TextEdit(15, 1, 'foobar'),
            ],
        ];
    }
}
