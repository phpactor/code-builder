<?php

namespace Phpactor\CodeBuilder\Domain\CodeFilter;

use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\CodeFilter;

class NullCodeFilter implements CodeFilter
{
    public function filter(Code $code): Code
    {
        return $code;
    }
}
