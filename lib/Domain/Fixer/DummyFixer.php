<?php

namespace Phpactor\CodeBuilder\Domain\Fixer;

use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\CodeBuilder\Domain\TextEdits;

class DummyFixer implements StyleFixer
{
    public function fix(string $text): TextEdits
    {
        return TextEdits::none();
    }
}
