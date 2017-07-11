<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\CodeBuilder\Domain\Prototype\Prototype;

interface Generator
{
    public function generate(Prototype $prototype): SourceCode;
}
