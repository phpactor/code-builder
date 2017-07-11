<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\Twig;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Phpactor\CodeBuilder\Adapter\Twig\TwigGenerator;
use Phpactor\CodeBuilder\Adapter\Twig\TemplateNameResolver;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Code;

class TwigGeneratorTest extends TestCase
{
    private $templateNameResolver;
    private $twig;
    private $generator;

    public function setUp()
    {
        $this->templateNameResolver = $this->prophesize(TemplateNameResolver::class);
        $this->prototype = $this->prophesize(Prototype::class);

        $this->templateNameResolver->resolveName($this->prototype->reveal())->willReturn('prototype');
        $this->twig = new Environment(new ArrayLoader([
            'prototype' => 'hello'
        ]));
        $this->generator = new TwigGenerator($this->twig, $this->templateNameResolver->reveal());
    }

    /**
     * @testdox It should use twig to generate a template
     */
    public function testGenerate()
    {
        $code = $this->generator->generate($this->prototype->reveal());
        $this->assertEquals(Code::fromString('hello'), $code);
    }

    /**
     * @testdox It can be instantiated without the name resolver.
     */
    public function testNoResolver()
    {
        new TwigGenerator($this->twig);
    }
}
