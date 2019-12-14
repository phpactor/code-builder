<?php

namespace Phpactor\CodeBuilder\Domain\Fixer;

use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\TextDocument\TextDocument;

class DummyFixer implements StyleFixer
{
    public function fix(string $text): string
    {
        return $text;
    }
}
