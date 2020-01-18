<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Domain\TextEdits;

interface StyleProposer
{
    public function onEnter(NodeQuery $node): TextEdits;

    public function onExit(NodeQuery $node): TextEdits;
}
