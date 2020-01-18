<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser\Fixer;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\MemberBlankLineProposer;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\TestUtils\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;

class MemberBlankLineProposerTest extends FixerTestCase
{
    protected function createProposer(): StyleProposer
    {
        return new MemberBlankLineProposer(new TextFormat());
    }
}
