<?php

namespace Phpactor\CodeBuilder\Domain\Fixer;

use Phpactor\CodeBuilder\Domain\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdits;

class NullFixer implements StyleProposer
{
    public function propose(string $text): TextEdits
    {
        return TextEdits::none();
    }
}
