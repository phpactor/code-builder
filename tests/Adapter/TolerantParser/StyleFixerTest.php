<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\DocblockIndentationFixer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\IndentationFixer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\MemberEmptyLineFixer;
use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\CodeBuilder\Tests\IntegrationTestCase;

class StyleFixerTest extends IntegrationTestCase
{
    /**
     * @var StyleFixer
     */
    private $fixer;

    protected function setUp(): void
    {
        $this->fixer = new StyleFixer(...[
            new MemberEmptyLineFixer(),
            new IndentationFixer(),
            new DocblockIndentationFixer()
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
