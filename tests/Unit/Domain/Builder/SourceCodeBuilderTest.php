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

        $class = $this->builder()->class('Dog')
            ->extends('Canine')
            ->implements('Teeth')
            ->property('one')->end()
            ->property('two')->end()
            ->method('method1')->end()
            ->method('method2')->end()
            ->build();

        $this->assertEquals('Canine', $class->extendsClass()->__toString());
        $this->assertEquals('Teeth', $class->implementsInterfaces()->first());
        $this->assertEquals('one', $class->properties()->first()->name());
        $this->assertEquals('method1', $class->methods()->first()->name());
    }

    public function testPropertyBuilder()
    {
        $builder = $this->builder();

        $property = $this->builder()->class('Dog')->property('one')
            ->type('string')
            ->defaultValue(null)
            ->build();

        $this->assertEquals('string', $property->type()->__toString());
        $this->assertEquals('null', $property->defaultValue()->export());
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
            [
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
            [
                $this->builder()->class('Dog')->method('one')->static()->abstract(),
                function ($method) {
                    $this->assertTrue($method->isStatic());
                    $this->assertTrue($method->isAbstract());
                }
            ],
            [
                $this->builder()->class('Dog')->method('one')->abstract(),
                function ($method) {
                    $this->assertFalse($method->isStatic());
                    $this->assertTrue($method->isAbstract());
                }
            ],
        ];
    }

    private function builder(): SourceCodeBuilder
    {
        return SourceCodeBuilder::create();
    }
}
