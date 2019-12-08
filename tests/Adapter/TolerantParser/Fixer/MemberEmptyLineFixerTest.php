<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser\Fixer;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\MemberEmptyLineFixer;
use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\TestUtils\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;

class MemberEmptyLineFixerTest extends FixerTestCase
{
    protected function createFixer(): StyleFixer
    {
        return new MemberEmptyLineFixer(new Parser());
    }
}
