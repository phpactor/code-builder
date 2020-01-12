<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Domain\TextEdits;

interface StyleProposer
{
    public function propose(NodeQuery $node): TextEdits;

    public function onExit(NodeQuery $node): TextEdits;
}
