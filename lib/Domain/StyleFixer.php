<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\TextDocument\TextDocument;

interface StyleFixer
{
    public function fix(TextDocument $document): TextDocument;
}
