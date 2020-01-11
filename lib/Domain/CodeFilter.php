<?php

namespace Phpactor\CodeBuilder\Domain;

interface CodeFilter
{
    public function filter(Code $code): Code;
}
