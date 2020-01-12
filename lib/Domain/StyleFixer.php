<?php

namespace Phpactor\CodeBuilder\Domain;

interface StyleFixer
{
    public function fix(string $code): string;
}
