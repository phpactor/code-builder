<?php

namespace Phpactor\CodeBuilder\Domain\Fixer;

use Phpactor\CodeBuilder\Domain\StyleFixer;

class DummyFixer implements StyleFixer
{
    public function fix(string $text): string
    {
        return $text;
    }
}
