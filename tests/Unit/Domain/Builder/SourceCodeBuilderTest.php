<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Builder;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;

class SourceCodeBuilderTest extends TestCase
{
    public function testSourceCodeBuilder()
    {
        $builder = $this->builder();
        $builder->namespace('Barfoo');
        $builder->use('Foobar');
        $builder->use('Foobar');
        $builder->use('Barfoo');
        $builder->class('Hello');

        $code = $builder->build();

        $this->assertInstanceOf(SourceCode::class, $code);
        $this->assertEquals('Barfoo', $code->namespace()->__toString());
        $this->assertCount(2, $code->useStatements());
        $this->assertEquals('Foobar', $code->useStatements()->first()->__toString());
        $this->assertEquals('Hello', $code->classes()->first()->name());
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
                    ->returnType('string')
                    ->visibility('private')
                    ->parameter('one')
                        ->type('One')
                        ->defaultValue(1)
                    ->end(),
                function ($method) {
                    $this->assertEquals('string', $method->returnType()->__toString());
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
}
