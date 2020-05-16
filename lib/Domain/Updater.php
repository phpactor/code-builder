<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\TextDocument\TextEdits;

interface Updater
{
    public function apply(Prototype $prototype, Code $code): TextEdits;
}
