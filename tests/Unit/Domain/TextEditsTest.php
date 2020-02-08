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
}
