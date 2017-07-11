<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;

class SourceCodeTest extends TestCase
{
    /**
     * @testdox Instantiate
     */
    public function testCreate()
    {
        new SourceCode();
    }
}
