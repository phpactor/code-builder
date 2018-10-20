<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Builder;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\Exception\InvalidBuilderException;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Builder\NamedBuilder;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use stdClass;

class MethodBuilderTest extends TestCase
{
    public function testExceptionOnAddNonParameterBuilder()
    {
        $this->expectException(InvalidBuilderException::class);
        $builder = $this->prophesize(NamedBuilder::class);
        SourceCodeBuilder::create()
            ->class('One')
            ->method('two')
            ->add($builder->reveal());
    }
}
