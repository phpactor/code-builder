<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Type;

class TypeTest extends TestCase
{
    public function testItReturnsANamespace()
    {
        $type = Type::fromString('Foo\\Bar');
        $this->assertEquals('Foo', $type->namespace());

        $type = Type::fromString('?Foo\\Bar', true);
        $this->assertEquals('Foo', $type->namespace());

        $type = Type::fromString('Bar');
        $this->assertNull($type->namespace());

        $type = Type::fromString('?Bar', true);
        $this->assertNull($type->namespace());

        $type = Type::none();
        $this->assertNull($type->namespace());
    }

    /**
     * @testdox It throws an exception if type is nullable but it is not allowed
     * @expectedException Phpactor\CodeBuilder\Domain\Type\Exception\TypeCannotBeNullableException
     */
    public function testItAllowsNullableOnlyIfExplicitelyPassed()
    {
        $type = Type::fromString('?Foo\\Bar');
    }

    public function testItAllowsNullable()
    {
        $type = Type::fromString('Foo\\Bar');
        $this->assertFalse($type->nullable());

        $type = Type::fromString('string');
        $this->assertFalse($type->nullable());

        $type = Type::fromString('Foo\\Bar', true);
        $this->assertFalse($type->nullable());

        $type = Type::fromString('string', true);
        $this->assertFalse($type->nullable());

        $type = Type::fromString('?Foo\\Bar', true);
        $this->assertTrue($type->nullable());

        $type = Type::fromString('?string', true);
        $this->assertTrue($type->nullable());
    }
}
