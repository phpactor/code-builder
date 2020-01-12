<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdits;

class DocblockIndentationProposer implements StyleProposer
{
    public function propose(NodeQuery $node): TextEdits
    {
        return TextEdits::none();
    }

    public function onExit(NodeQuery $node): TextEdits
    {
        return TextEdits::none();
    }
}
