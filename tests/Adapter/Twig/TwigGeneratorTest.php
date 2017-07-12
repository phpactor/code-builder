<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\Twig;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Phpactor\CodeBuilder\Tests\Adapter\GeneratorTestCase;
use Phpactor\CodeBuilder\Domain\Generator;
use Phpactor\CodeBuilder\Adapter\Twig\TwigGenerator;


class TwigGeneratorTest extends GeneratorTestCase
{
    private $twig;

    public function setUp()
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../templates'), [
            'strict_variables' => true,
        ]);
    }

    protected function generator(): Generator
    {
        return new TwigGenerator($this->twig);
    }
}

