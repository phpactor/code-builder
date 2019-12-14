<?php

namespace Phpactor\CodeBuilder\Domain\Fixer;

use Phpactor\CodeBuilder\Domain\StyleFixer;

class ChainFixer implements StyleFixer
{
    /**
     * @var StyleFixer[]
     */
    private $fixers;

    public function __construct(StyleFixer ...$fixers)
    {
        $this->fixers = $fixers;
    }

    public function fix(string $text): string
    {
        foreach ($this->fixers as $fixer) {
            $text = $fixer->fix($text);
        }

        return $text;
    }
}
