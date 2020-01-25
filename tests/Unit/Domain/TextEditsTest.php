<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;

class TextEditsTest extends TestCase
{
    /**
     * @dataProvider provideMerge
     */
    public function testMerge(array $edits1, array $edits2, array $expectedEdits)
    {
        self::assertEquals(
            TextEdits::fromTextEdits($expectedEdits),
            TextEdits::fromTextEdits($edits1)->merge(
                TextEdits::fromTextEdits($edits2)
            )
        );
    }

    public function provideMerge()
    {
        yield 'empty' => [
            [
            ],
            [
            ],
            [
            ],
        ];

        yield 'empty merge does not affect existing data' => [
            [
                new TextEdit(1, 5, 'foobar'),
                new TextEdit(2, 5, 'foobar'),
            ],
            [
            ],
            [
                new TextEdit(1, 5, 'foobar'),
                new TextEdit(2, 5, 'foobar'),
            ],
        ];

        yield 'original edits are ordered before subsequent edits with same offset' => [
            [
                new TextEdit(1, 5, 'foobar'),
                new TextEdit(2, 5, 'foobar'),
            ],
            [
                new TextEdit(1, 5, 'barfoo'),
                new TextEdit(2, 5, 'barfoo'),
            ],
            [
                new TextEdit(1, 5, 'foobar'),
                new TextEdit(1, 5, 'barfoo'),
                new TextEdit(2, 5, 'foobar'),
                new TextEdit(2, 5, 'barfoo'),
            ],
        ];

        yield 'text edits are sorted' => [
            [
                new TextEdit(2, 5, 'foobar'),
                new TextEdit(3, 5, 'foobar'),
            ],
            [
                new TextEdit(1, 5, 'barfoo'),
                new TextEdit(2, 5, 'barfoo'),
            ],
            [
                new TextEdit(1, 5, 'barfoo'),
                new TextEdit(2, 5, 'foobar'),
                new TextEdit(2, 5, 'barfoo'),
                new TextEdit(3, 5, 'foobar'),
            ],
        ];
    }

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

        yield 'intersect 0' => [
            [
                new TextEdit(0, 5, 'foobar'),
            ],
            [
                new TextEdit(5, 6, 'foobar'),
            ],
            [
                new TextEdit(0, 5, 'foobar'),
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
                new TextEdit(4, 6, 'foobar'),
                new TextEdit(15, 6, 'foobar'),
                new TextEdit(16, 6, 'foobar'),
            ],
            [
                new TextEdit(0, 5, 'foobar'),
                new TextEdit(15, 1, 'foobar'),
            ],
        ];

        yield 'preserve order for edits on same offset' => [
            [
                new TextEdit(18, 2, 'foobar'),
                new TextEdit(15, 1, 'foobar'),
            ],
            [
                new TextEdit(18, 6, 'foobar'),
                new TextEdit(15, 6, 'foobar'),
            ],
            [
                new TextEdit(18, 2, 'foobar'),
                new TextEdit(15, 1, 'foobar'),
            ],
        ];
    }

    /**
     * @dataProvider provideAppliedTextEdits
     */
    public function testAppliedTextEdits(array $edits1, array $expectedEdits)
    {
        self::assertEquals(
            TextEdits::fromTextEdits($expectedEdits),
            TextEdits::fromTextEdits($edits1)->appliedTextEdits()
        );
    }

    public function provideAppliedTextEdits()
    {
        yield 'empty' => [
            [
            ],
            [
            ],
            [
            ],
        ];

        yield 'no effect on static change' => [
            [
                new TextEdit(0, 0, ''),
                new TextEdit(0, 0, ''),
            ],
            [
                new TextEdit(0, 0, ''),
                new TextEdit(0, 0, ''),
            ],
        ];

        yield 'advances length on insert 1' => [
            [
                new TextEdit(0, 0, 'foobar'),
            ],
            [
                new TextEdit(0, 6, 'foobar'),
            ],
        ];

        yield 'advances length on insert 2' => [
            [
                new TextEdit(0, 0, 'foobar'),
                new TextEdit(6, 12, 'foobar'),
            ],
            [
                new TextEdit(0, 6, 'foobar'),
                new TextEdit(12, 6, 'foobar'),
            ],
        ];

        yield 'decreases start on deletions' => [
            [
                new TextEdit(6, 5, 'a'),
                new TextEdit(12, 1, 'b'),
            ],
            [
                new TextEdit(6, 1,  'a'),
                new TextEdit(8, 1,  'b'),
            ],
        ];
    }

    /**
     * @dataProvider provideIntegrate
     */
    public function testIntegrate(array $original, array $new, array $expectedEdits)
    {
        self::assertEquals(
            TextEdits::fromTextEdits($expectedEdits),
            TextEdits::fromTextEdits($original)->integrate(TextEdits::fromTextEdits($new))
        );
    }

    public function provideIntegrate()
    {
        yield 'empty' => [
            [
            ],
            [
            ],
            [
            ],
            [
            ],
        ];

        yield 'removes duplicates' => [
            [
                new TextEdit(0, 0, ''),
                new TextEdit(0, 0, ''),
            ],
            [
                new TextEdit(0, 0, ''),
                new TextEdit(0, 0, ''),
            ],
            [
                new TextEdit(0, 0, ''),
                new TextEdit(0, 0, ''),
            ],
        ];

        yield 'applies insertion offset' => [
            [
                new TextEdit(10, 0, ''),
                new TextEdit(12, 0, ''),
            ],
            [
                new TextEdit(0, 0, '  '),
            ],
            [
                new TextEdit(0, 0, '  '),
                new TextEdit(12, 0, ''),
                new TextEdit(14, 0, ''),
            ],
        ];
    }
}
