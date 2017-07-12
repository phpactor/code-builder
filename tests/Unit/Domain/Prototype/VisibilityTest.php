<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;

class VisibilityTest extends TestCase
{
    /**
     * @testdox It throws an exception if an invalid visiblity is given.
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid visibility
     */
    public function testException()
    {
        Visibility::fromString('foobar');
    }
}
