<?php

namespace Phpactor\CodeBuilder\Adapter\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;

class TwigExtension extends AbstractExtension
{
    /**
     * @var TwigGenerator
     */
    private $generator;

    /**
     * @var int
     */
    private $indentation;

    public function __construct(TwigRenderer $generator, string $indentation = '    ')
    {
        $this->generator = $generator;
        $this->indentation = $indentation;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('indent', [ $this, 'indent' ]),
        ];
    }

    public function indent(string $string, int $level = 0)
    {
        $lines = explode(PHP_EOL, $string);
        $lines = array_map(function ($line) use ($level) {
            return str_repeat($this->indentation, $level) . $line;
        }, $lines);

        return implode(PHP_EOL, $lines);
    }
}

