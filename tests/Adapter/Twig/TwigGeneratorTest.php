<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\Twig;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Phpactor\CodeBuilder\Tests\Adapter\GeneratorTestCase;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Adapter\Twig\TwigGenerator;
use Phpactor\CodeBuilder\Adapter\Twig\TwigExtension;


class TwigGeneratorTest extends GeneratorTestCase
{
    private $twig;

    public function setUp()
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../templates'), [
            'strict_variables' => true,
        ]);
        $this->twig->addExtension(new TwigExtension($this->renderer(), '    '));
    }

    protected function renderer(): Renderer
    {
        static $generator;

        if ($generator) {
            return $generator;
        }

        $generator = new TwigGenerator($this->twig);

        return $generator;
    }
}

