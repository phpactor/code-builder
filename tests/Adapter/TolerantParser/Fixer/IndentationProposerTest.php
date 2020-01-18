<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser\Fixer;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\IndentationFixer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\MemberEmptyLineFixer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\IndentationProposer;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\TestUtils\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;

class IndentationProposerTest extends FixerTestCase
{
    protected function createProposer(): StyleProposer
    {
        return new IndentationProposer(new TextFormat());
    }
}
