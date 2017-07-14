<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\Twig;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Phpactor\CodeBuilder\Tests\Adapter\GeneratorTestCase;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\CodeBuilder\Adapter\Twig\TwigExtension;


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
}

