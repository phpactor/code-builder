<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\DocblockIndentationProposer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\IndentationProposer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\MemberBlankLineProposer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantStyleFixer;
use Phpactor\CodeBuilder\Tests\IntegrationTestCase;
use Phpactor\CodeBuilder\Util\TextFormat;

class StyleFixerTest extends IntegrationTestCase
{
    /**
     * @var StyleFixer
     */
    private $fixer;

    protected function setUp(): void
    {
        $this->fixer = new TolerantStyleFixer(null, ...[
            new MemberBlankLineProposer(new TextFormat()),
            new IndentationProposer(new TextFormat()),
            new DocblockIndentationProposer(new TextFormat())
        ]);
    }

    public function testFix()
    {
        $code = <<<'EOT'
<?php
class Foo
{
    private $foo;
}
EOT
        ;
        $expected = <<<'EOT'
<?php
class Foo
{
    private $foo;
}
EOT
        ;
        $fixed = $this->fixer->fix($code);
        self::assertEquals($expected, $fixed);
    }
}
