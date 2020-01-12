<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser\Fixer;

use Phpactor\CodeBuilder\Util\TextFormat;

use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\DocblockIndentationProposer;

use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

class DocblockIndentationFixerTest extends FixerTestCase
{
    protected function createProposer(): StyleProposer
    {
        return new DocblockIndentationProposer(new TextFormat());
    }
}
