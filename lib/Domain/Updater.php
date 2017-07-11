<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Code;

interface Updater
{
    public function apply(Prototype $prototype, Code $code): Code;
}
