<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\Twig;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Phpactor\CodeBuilder\Tests\Adapter\GeneratorTestCase;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\CodeBuilder\Adapter\Twig\TwigExtension;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;


class TwigGeneratorTest extends GeneratorTestCase
{
    protected function renderer(): Renderer
    {
        static $generator;

        if ($generator) {
            return $generator;
        }

        $generator = new TwigRenderer();

        return $generator;
    }

    /**
     * @testdox It should fallback to the default templates if variant template
     *          does not exist.
     */
    public function testFallback()
    {
        $builder = SourceCodeBuilder::create();
        $source = $this->renderer()->render($builder->build(), 'unknown');
        $this->assertEquals('<?php', (string) $source);
    }
}

