<?php

namespace Phpactor\CodeBuilder\Domain;

interface StyleProposer
{
    public function propose(string $text): TextEdits;
}
