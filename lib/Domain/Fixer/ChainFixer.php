<?php

namespace Phpactor\CodeBuilder\Domain\Fixer;

use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\CodeBuilder\Domain\TextEdits;

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

    public function fix(string $text): TextEdits
    {
        $edits = TextEdits::none();
        foreach ($this->fixers as $fixer) {
            $edits = $edits->merge($fixer->fix($text));
        }

        return $edits;
    }
}
