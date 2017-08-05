<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\MethodHeader;

class MethodTest extends TestCase
{
    /**
     * @testfox It returns if it is static or abstract
     */
    public function testAbstractStatic()
    {
        $method = $this->createMethodModifier(MethodHeader::IS_STATIC);
        $this->assertTrue($method->isStatic());
        $this->assertFalse($method->isAbstract());

        $method = $this->createMethodModifier(MethodHeader::IS_ABSTRACT);
        $this->assertTrue($method->isAbstract());
        $this->assertFalse($method->isStatic());

        $method = $this->createMethodModifier(MethodHeader::IS_ABSTRACT|MethodHeader::IS_STATIC);
        $this->assertTrue($method->isAbstract());
        $this->assertTrue($method->isStatic());
    }

    private function createMethodModifier($modifier)
    {
        return new MethodHeader('test', null, null, null, null, $modifier);
    }
}
