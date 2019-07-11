<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Collection;

class CollectionTest extends TestCase
{
    /**
     * @testdox Get throws exception
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown test "foo", known items
     */
    public function testGetException()
    {
        $collection = TestCollection::fromArray([
            'one' => new \stdClass()
        ]);

        $collection->get('foo');
    }
}

class TestCollection extends Collection
{
    public static function fromArray(array $items)
    {
        return new self($items);
    }

    protected function singularName(): string
    {
        return 'test';
    }
}
