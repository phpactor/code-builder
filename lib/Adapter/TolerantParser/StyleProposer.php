<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Phpactor\CodeBuilder\Domain\TextEdits;

interface StyleProposer
{
    public function propose(NodeQuery $node): TextEdits;
}
