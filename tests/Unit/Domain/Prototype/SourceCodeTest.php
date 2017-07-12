<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatements;
use Phpactor\CodeBuilder\Domain\Prototype\Classes;

class SourceCodeTest extends TestCase
{
    /**
     * @testdox Instantiate
     */
    public function testCreate()
    {
        new SourceCode();
    }

    public function testAccessors()
    {
        $namespace = NamespaceName::fromString('Ducks');
        $useStatements = UseStatements::empty();
        $classes = Classes::empty();


        $code = new SourceCode($namespace, $useStatements, $classes);
        $this->assertSame($namespace, $code->namespace());
        $this->assertSame($useStatements, $code->useStatements());
        $this->assertSame($classes, $code->classes());
    }
}
