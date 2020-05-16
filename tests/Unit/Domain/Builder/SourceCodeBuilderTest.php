<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Builder;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;

class SourceCodeBuilderTest extends TestCase
{
    /**
     * @dataProvider provideModificationTracking
     */
    public function testModificationTracking(\Closure $setup, \Closure $assertion)
    {
        $builder = $this->builder();
        $setup($builder);
        $assertion($builder);

        $builder->class('Hello')->method('goodbye');
        $this->assertTrue($builder->isModified(), 'method has been modified since last snapshot');
    }

    public function provideModificationTracking()
    {
        yield 'new builder is modified by default' => [
            function (SourceCodeBuilder $builder) {
                $builder->class('foobar');
            },
            function (SourceCodeBuilder $builder) {
                $this->assertTrue($builder->isModified());
            }
        ];

        yield 'is not modified after snapshot' => [
            function (SourceCodeBuilder $builder) {
                $builder->class('foobar');
                $builder->snapshot();
            },
            function (SourceCodeBuilder $builder) {
                $this->assertFalse($builder->isModified());
            }
        ];

        yield 'is not modified if updated values are the same 1' => [
            function (SourceCodeBuilder $builder) {
                $builder->class('foobar')->method('foobar')->parameter('barfoo');
                $builder->snapshot();
                $builder->class('foobar')->method('foobar')->parameter('barfoo');
            },
            function (SourceCodeBuilder $builder) {
                $this->assertFalse($builder->isModified());
            }
        ];

        yield 'is not modified if updated values are the same 2' => [
            function (SourceCodeBuilder $builder) {
                $builder->class('foobar')->method('foobar');
                $builder->snapshot();
                $builder->class('foobar')->method('foobar');
            },
            function (SourceCodeBuilder $builder) {
                $this->assertFalse($builder->isModified());
            }
        ];

        yield 'is modified when values are different 1' => [
            function (SourceCodeBuilder $builder) {
                $builder->class('foobar')->method('foobar');
                $builder->snapshot();
                $builder->class('foobar')->method('barbarr');
            },
            function (SourceCodeBuilder $builder) {
                $this->assertTrue($builder->isModified());
            }
        ];

        yield 'is modified when values are different 2' => [
            function (SourceCodeBuilder $builder) {
                $builder->class('foobar')->method('foobar')->parameter('barbar');
                $builder->snapshot();
                $builder->class('foobar')->method('barbar')->parameter('fofo');
            },
            function (SourceCodeBuilder $builder) {
                $this->assertTrue($builder->isModified());
            }
        ];
    }

    public function testSourceCodeBuilderUse()
    {
        $builder = $this->builder();
        $builder->namespace('Barfoo');
        $builder->use('Foobar');
        $builder->use('Foobar');
        $builder->use('Barfoo');
        $builder->class('Hello');
        $builder->trait('Goodbye');

        $code = $builder->build();

        $this->assertInstanceOf(SourceCode::class, $code);
        $this->assertEquals('Barfoo', $code->namespace()->__toString());
        $this->assertCount(2, $code->useStatements());
        $this->assertEquals('Barfoo', $code->useStatements()->sorted()->first()->__toString());
        $this->assertEquals('Foobar', $code->useStatements()->first()->__toString());
        $this->assertEquals('Hello', $code->classes()->first()->name());
        $this->assertEquals('Goodbye', $code->traits()->first()->name());
    }

    public function testFunctionUse()
    {
        $builder = $this->builder();
        $builder->useFunction('hello');
        $builder->useFunction('hello\goodbye');
        $code = $builder->build();

        $this->assertCount(2, $code->useStatements());
        $this->assertEquals('hello', $code->useStatements()->first()->__toString());
        $this->assertEquals(UseStatement::TYPE_FUNCTION, $code->useStatements()->first()->type());
    }


    public function testClassBuilder()
    {
        $builder = $this->builder();
        $classBuilder = $builder->class('Dog')
            ->extends('Canine')
            ->implements('Teeth')
            ->property('one')->end()
            ->property('two')->end()
            ->method('method1')->end()
            ->method('method2')->end();

        $class = $classBuilder->build();

        $this->assertSame($classBuilder, $builder->class('Dog'));
        $this->assertEquals('Canine', $class->extendsClass()->__toString());
        $this->assertEquals('Teeth', $class->implementsInterfaces()->first());
        $this->assertEquals('one', $class->properties()->first()->name());
        $this->assertEquals('method1', $class->methods()->first()->name());
    }

    public function testClassBuilderAddMethodBuilder()
    {
        $builder = $this->builder();
        $methodBuilder = $this->builder()->class('Cat')->method('Whiskers');
        $classBuilder = $builder->class('Dog');
        $classBuilder->add($methodBuilder);

        $this->assertSame($classBuilder->method('Whiskers'), $methodBuilder);
    }

    public function testClassBuilderAddPropertyBuilder()
    {
        $builder = $this->builder();
        $propertyBuilder = $this->builder()->class('Cat')->property('whiskers');
        $classBuilder = $builder->class('Dog');
        $classBuilder->add($propertyBuilder);

        $this->assertSame($classBuilder->property('whiskers'), $propertyBuilder);
    }

    public function testInterfaceBuilder()
    {
        $builder = $this->builder();
        $interfaceBuilder = $builder->interface('Dog')
            ->extends('Canine')
            ->method('method1')->end()
            ->method('method2')->end();

        $class = $interfaceBuilder->build();

        $this->assertSame($interfaceBuilder, $builder->interface('Dog'));
    }

    public function testTraitBuilder()
    {
        $builder = $this->builder();
        $traitBuilder = $builder->trait('Dog')
            ->property('one')->end()
            ->property('two')->end()
            ->method('method1')->end()
            ->method('method2')->end();

        $trait = $traitBuilder->build();

        $this->assertSame($traitBuilder, $builder->trait('Dog'));
        $this->assertEquals('one', $trait->properties()->first()->name());
        $this->assertEquals('method1', $trait->methods()->first()->name());
    }

    public function testTraitBuilderAddMethodBuilder()
    {
        $builder = $this->builder();
        $methodBuilder = $this->builder()->trait('Cat')->method('Whiskers');
        $traitBuilder = $builder->trait('Dog');
        $traitBuilder->add($methodBuilder);

        $this->assertSame($traitBuilder->method('Whiskers'), $methodBuilder);
    }

    public function testTraitBuilderAddPropertyBuilder()
    {
        $builder = $this->builder();
        $propertyBuilder = $this->builder()->trait('Cat')->property('whiskers');
        $traitBuilder = $builder->trait('Dog');
        $traitBuilder->add($propertyBuilder);

        $this->assertSame($traitBuilder->property('whiskers'), $propertyBuilder);
    }

    public function testPropertyBuilder()
    {
        $builder = $this->builder();
        $propertyBuilder = $builder->class('Dog')->property('one')
            ->type('string')
            ->defaultValue(null);

        $property = $propertyBuilder->build();

        $this->assertEquals('string', $property->type()->__toString());
        $this->assertEquals('null', $property->defaultValue()->export());
        $this->assertSame($propertyBuilder, $builder->class('Dog')->property('one'));
    }

    public function testClassMethodBuilderAccess()
    {
        $builder = $this->builder();
        $methodBuilder = $builder->class('Bar')->method('foo');

        $this->assertSame($methodBuilder, $builder->class('Bar')->method('foo'));
    }

    public function testTraitMethodBuilderAccess()
    {
        $builder = $this->builder();
        $methodBuilder = $builder->trait('Bar')->method('foo');

        $this->assertSame($methodBuilder, $builder->trait('Bar')->method('foo'));
    }

    /**
     * @dataProvider provideMethodBuilder
     */
    public function testMethodBuilder(MethodBuilder $methodBuilder, \Closure $assertion)
    {
        $builder = $this->builder();
        $method = $methodBuilder->build();
        $assertion($method);
    }

    public function provideMethodBuilder()
    {
        return [
            'Method return type' => [
                $this->builder()->class('Dog')->method('one')
                ->returnType('?string')
                ->visibility('private')
                ->parameter('one')
                ->type('One')
                ->defaultValue(1)
                ->end(),
                function (Method $method) {
                    $this->assertEquals('string', $method->returnType()->__toString());
                    $this->assertTrue($method->returnType()->nullable());
                }
        ],
            'Method mofifiers 1' => [
                $this->builder()->class('Dog')->method('one')->static()->abstract(),
                function ($method) {
                    $this->assertTrue($method->isStatic());
                    $this->assertTrue($method->isAbstract());
                }
        ],
            'Method mofifiers 2' => [
                $this->builder()->class('Dog')->method('one')->abstract(),
                function ($method) {
                    $this->assertFalse($method->isStatic());
                    $this->assertTrue($method->isAbstract());
                }
        ],
            'Method lines' => [
                $this->builder()->class('Dog')->method('one')->body()->line('one')->line('two')->end(),
                function ($method) {
                    $this->assertCount(2, $method->body()->lines());
                    $this->assertEquals('one', (string) $method->body()->lines()->first());
                }
        ],
        ];
    }

    public function testParameterBuilder()
    {
        $builder = $this->builder();
        $method = $builder->class('Bar')->method('foo');
        $parameterBuilder = $method->parameter('foo');

        $this->assertSame($parameterBuilder, $method->parameter('foo'));
    }

    private function builder(): SourceCodeBuilder
    {
        return SourceCodeBuilder::create();
    }

    private function assertInstanceOfAndPopNode($className, Generator $nodes)
    {
        $this->assertInstanceOf($className, $nodes->current());
        $nodes->next();
    }
}
