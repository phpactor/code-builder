<?php

namespace Phpactor\CodeBuilder\Domain\StyleFixer;

use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\CodeBuilder\Domain\TextEdits;

class NullStyleFixer implements StyleFixer
{
    public function fix(string $code): string
    {
        return $code;
    }

    public function fixIntersection(TextEdits $edits, string $code): string
    {
        return $code;
    }
}
