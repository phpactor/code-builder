<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\WorseReflection;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\WorseReflection\ReflectorBuilder;

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
        $source = $this->build('<?php class Foobar { public $foo; }');
        $this->assertCount(1, $source->classes()->first()->properties());
        $this->assertEquals('foo', $source->classes()->first()->properties()->first()->name());
    }

    public function testClassWithProtectedProperty()
    {
        $source = $this->build('<?php class Foobar { private $foo; }');
        $this->assertCount(1, $source->classes()->first()->properties());
        $this->assertEquals('private', (string) $source->classes()->first()->properties()->first()->visibility());
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

    public function testClassWithPropertyImportedNullableType()
    {
        $source = $this->build('<?php namespace Test; use Bar\Foobar; class Foobar { private ?Foobar $foo = "foobar"; }');
        $this->assertEquals(
            '?Foobar',
            (string) $source->classes()->first()->properties()->first()->type()
        );
        $this->assertEquals('Bar\Foobar', (string) $source->useStatements()->first());
    }

    public function testSimpleTrait()
    {
        $source = $this->build('<?php trait Foobar {}');
        $traits = $source->traits();
        $this->assertCount(1, $traits);
        $this->assertEquals('Foobar', $traits->first()->name());
    }

    public function testSimpleTraitWithNamespace()
    {
        $source = $this->build('<?php namespace Foobar; trait Foobar {}');
        $traits = $source->traits();
        $this->assertCount(1, $traits);
        $this->assertEquals('Foobar', $source->namespace());
    }

    public function testTraitWithProperty()
    {
        $source = $this->build('<?php trait Foobar { public $foo; }');
        $this->assertCount(1, $source->traits()->first()->properties());
        $this->assertEquals('foo', $source->traits()->first()->properties()->first()->name());
    }

    public function testTraitWithMethod()
    {
        $source = $this->build('<?php trait Foobar { public function method() {} }');
        $this->assertEquals('method', $source->traits()->first()->methods()->first()->name());
    }

    public function testMethod()
    {
        $source = $this->build('<?php class Foobar { public function method() {} }');
        $this->assertEquals('method', $source->classes()->first()->methods()->first()->name());
    }

    public function testNoVirtualMethod()
    {
        $source = $this->build('<?php /** @method stdClass foobar() */class Foobar {  }');
        $this->assertCount(0, $source->classes()->first()->methods());
    }

    public function testMethodWithReturnType()
    {
        $source = $this->build('<?php class Foobar { public function method(): string {} }');
        $this->assertEquals('string', $source->classes()->first()->methods()->first()->returnType());
    }

    public function testMethodWithNullableReturnType()
    {
        $source = $this->build('<?php class Foobar { public function method(): ?string {} }');
        $this->assertEquals('?string', $source->classes()->first()->methods()->first()->returnType());
    }

    public function testMethodWithImportedNullableReturnType()
    {
        $source = $this->build('<?php namespace Test; use Bar\Baz; class Foobar { public function method(): ?Baz {} }');
        $this->assertEquals('?Baz', $source->classes()->first()->methods()->first()->returnType());
        $this->assertEquals('Bar\Baz', (string) $source->useStatements()->first());
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

    public function testMethodWithParameterByReference()
    {
        $source = $this->build('<?php class Foobar { public function method(&$param) {} }');
        $this->assertTrue($source->classes()->first()->methods()->first()->parameters()->first()->byReference());
    }

    public function testMethodWithTypedParameter()
    {
        $source = $this->build('<?php class Foobar { public function method(string $param) {} }');
        $this->assertEquals('string', (string) $source->classes()->first()->methods()->first()->parameters()->first()->type());
    }

    public function testMethodWithNullableTypedParameter()
    {
        $source = $this->build('<?php class Foobar { public function method(?string $param) {} }');
        $this->assertEquals('?string', (string) $source->classes()->first()->methods()->first()->parameters()->first()->type());
    }

    public function testMethodWithAliasedParameter()
    {
        $source = $this->build('<?php use Foobar as Barfoo; class Foobar { public function method(Barfoo $param) {} }');
        $this->assertEquals('Barfoo', (string) $source->classes()->first()->methods()->first()->parameters()->first()->type());
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

    public function testStaticMethod()
    {
        $source = $this->build('<?php class Foobar { public static function method($param = "1234") {} }');
        $this->assertTrue($source->classes()->first()->methods()->first()->isStatic());
    }

    public function testClassWhichExtendsClassWithMethods()
    {
        $source = $this->build(
            <<<'EOT'
<?php
class Foobar
{
    protected $bar;

    public function method()
    {
    }
}

class BarBar extends Foobar
{
}
EOT
        );
        $this->assertCount(0, $source->classes()->get('BarBar')->methods());
        $this->assertCount(0, $source->classes()->get('BarBar')->properties());
    }

    public function testInterface()
    {
        $source = $this->build('<?php interface Foobar {}');
        $this->assertEquals('Foobar', (string) $source->interfaces()->first()->name());
    }

    public function testInterfaceWithMethod()
    {
        $source = $this->build('<?php interface Foobar { public function hello(World $world); }');
        $this->assertEquals('hello', (string) $source->interfaces()->first()->methods()->get('hello')->name());
    }

    private function build(string $source): SourceCode
    {
        $reflector = ReflectorBuilder::create()
            ->addMemberProvider(new DocblockMemberProvider())
            ->addSource($source)->build();

        $worseFactory = new WorseBuilderFactory($reflector);
        return $worseFactory->fromSource($source)->build();
    }
}
