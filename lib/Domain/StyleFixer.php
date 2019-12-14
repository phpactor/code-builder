<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\TextDocument\TextDocument;

interface StyleFixer
{
    public function fix(string $text): string;
}
