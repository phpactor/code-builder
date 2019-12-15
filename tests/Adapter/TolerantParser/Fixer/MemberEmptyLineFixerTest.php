<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser\Fixer;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\MemberEmptyLineFixer;
use Phpactor\CodeBuilder\Domain\StyleProposer;
use Phpactor\TestUtils\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;

class MemberEmptyLineFixerTest extends FixerTestCase
{
    protected function createFixer(): StyleProposer
    {
        return new MemberEmptyLineFixer(new Parser());
    }
}
