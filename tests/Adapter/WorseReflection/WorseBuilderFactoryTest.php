<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\WorseReflection;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\CodeBuilder\SourceBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;

class WorseBuilderFactoryTest extends TestCase
{
    public function testEmptySource()
    {
        $source = $this->build('<?php ');
        $this->assertInstanceOf(SourceCode::class, $source);
    }

    public function testSimpleClass()
    {
        $source = $this->build('<?php class Foobar {}');
        $classes = $source->classes();
        $this->assertCount(1, $classes);
        $this->assertEquals('Foobar', $classes->first()->name());

    }

    public function testSimpleClassWithNamespace()
    {
        $source = $this->build('<?php namespace Foobar; class Foobar {}');
        $classes = $source->classes();
        $this->assertCount(1, $classes);
        $this->assertEquals('Foobar', $source->namespace());
    }

    public function testClassWithProperty()
    {
        $source = $this->build('<?php class Foobar { private $foo; }');
        $this->assertCount(1, $source->classes()->first()->properties());
        $this->assertEquals('foo', $source->classes()->first()->properties()->first()->name());
    }

    public function testClassWithPropertyDefaultValue()
    {
        $this->markTestSkipped('Worse reflection doesn\'t support default property values atm');
        $source = $this->build('<?php class Foobar { private $foo = "foobar"; }');
        $this->assertEquals('foobar', $source->classes()->first()->properties()->first()->defaultValue()->export());
    }

    public function testClassWithPropertyTyped()
    {
        $source = $this->build('<?php class Foobar { /** @var Foobar */private $foo = "foobar"; }');
        $this->assertEquals('Foobar', $source->classes()->first()->properties()->first()->type()->__toString());
    }

    public function testClassWithPropertyScalarTyped()
    {
        $source = $this->build('<?php class Foobar { /** @var string */private $foo = "foobar"; }');
        $this->assertEquals('string', $source->classes()->first()->properties()->first()->type()->__toString());
    }

    public function testClassWithPropertyImportedType()
    {
        $source = $this->build('<?php use Bar\Foobar; class Foobar { /** @var Foobar */private $foo = "foobar"; }');
        $this->assertEquals('Foobar', $source->classes()->first()->properties()->first()->type()->__toString());
        $this->assertEquals('Bar\Foobar', (string) $source->useStatements()->first());
    }

    public function testMethod()
    {
        $source = $this->build('<?php class Foobar { public function method() {} }');
        $this->assertEquals('method', $source->classes()->first()->methods()->first()->name());
    }

    public function testMethodWithReturnType()
    {
        $source = $this->build('<?php class Foobar { public function method(): string {} }');
        $this->assertEquals('string', $source->classes()->first()->methods()->first()->returnType());
    }

    public function testMethodProtected()
    {
        $source = $this->build('<?php class Foobar { protected function method() {} }');
        $this->assertEquals('protected', $source->classes()->first()->methods()->first()->visibility());
    }

    public function testMethodWithParameter()
    {
        $source = $this->build('<?php class Foobar { public function method($param) {} }');
        $this->assertEquals('param', $source->classes()->first()->methods()->first()->parameters()->first()->name());
    }

    public function testMethodWithTypedParameter()
    {
        $source = $this->build('<?php class Foobar { public function method(string $param) {} }');
        $this->assertEquals('string', (string) $source->classes()->first()->methods()->first()->parameters()->first()->type());
    }

    public function testMethodWithDefaultValue()
    {
        $source = $this->build('<?php class Foobar { public function method($param = 1234) {} }');
        $this->assertEquals(1234, (string) $source->classes()->first()->methods()->first()->parameters()->first()->defaultValue()->value());
    }

    public function testMethodWithDefaultValueQuoted()
    {
        $source = $this->build('<?php class Foobar { public function method($param = "1234") {} }');
        $this->assertEquals('1234', (string) $source->classes()->first()->methods()->first()->parameters()->first()->defaultValue()->value());
    }

    private function build(string $source): SourceCode
    {
        $reflector = Reflector::create();

        $worseFactory = new WorseBuilderFactory($reflector);
        return $worseFactory->fromSource($source)->build();
    }
}
