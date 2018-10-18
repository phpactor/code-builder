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

        $type = Type::fromString('Bar');
        $this->assertNull($type->namespace());

        $type = Type::none();
        $this->assertNull($type->namespace());
    }
}
