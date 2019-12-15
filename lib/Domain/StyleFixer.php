<?php

namespace Phpactor\CodeBuilder\Domain;

interface StyleFixer
{
    public function propose(string $text): TextEdits;
}
