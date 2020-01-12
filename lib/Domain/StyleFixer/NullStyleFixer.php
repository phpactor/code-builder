<?php

namespace Phpactor\CodeBuilder\Domain\StyleFixer;

use Phpactor\CodeBuilder\Domain\StyleFixer;

class NullStyleFixer implements StyleFixer
{
    public function fix(string $code): string
    {
        return $code;
    }
}
