<?php

namespace Phpactor\CodeBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\SourceBuilder;
use Phpactor\CodeBuilder\Domain\Generator;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Prototype;
use Phpactor\CodeBuilder\Domain\Code;

class SourceBuilderTest extends TestCase
{
    private $updater;
    private $builder;
    private $generator;

    private $prototype;

    public function setUp()
    {
        $this->generator = $this->prophesize(Generator::class);
        $this->updater = $this->prophesize(Updater::class);
        $this->builder = new SourceBuilder(
            $this->generator->reveal(),
            $this->updater->reveal()
        );
        $this->prototype = $this->prophesize(Prototype\Prototype::class);
    }

    /**
     * @testdoc It should delegate to the generator.
     */
    public function testGenerate()
    {
        $expectedCode = Code::fromString('');
        $this->generator->generate($this->prototype->reveal())->willReturn($expectedCode);
        $code = $this->builder->generate($this->prototype->reveal());

        $this->assertSame($expectedCode, $code);
    }

    /**
     * @testdoc It should delegate to the updater.
     */
    public function testUpdate()
    {
        $sourceCode = Code::fromString('');
        $expectedCode = Code::fromString('');
        $this->updater->apply($this->prototype->reveal(), $sourceCode)->willReturn($expectedCode);
        $code = $this->builder->apply($this->prototype->reveal(), $sourceCode);

        $this->assertSame($expectedCode, $code);
    }
}
