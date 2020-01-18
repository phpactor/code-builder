<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser\Fixer;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\MemberEmptyLineFixer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantStyleFixer;
use Phpactor\CodeBuilder\Tests\IntegrationTestCase;
use Phpactor\TestUtils\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;
use RuntimeException;

abstract class FixerTestCase extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @dataProvider provideProposer
     */
    public function testProposer(string $path)
    {
        $this->workspace()->loadManifest(file_get_contents($path));
        $document = TextDocumentBuilder::create($this->workspace()->getContents('source.php'))->build();
        $fixed = (new TolerantStyleFixer([
            $this->createProposer()
        ]))->fix($document->__toString());

        self::assertEquals(trim($this->workspace()->getContents('expected.php')), trim($fixed));
    }

    public function provideProposer(): Generator
    {
        $name = basename(str_replace('\\', '/', get_class($this->createProposer())));
        yield from $this->yieldExamplesIn(__DIR__ . '/examples/' . $name);
    }

    abstract protected function createProposer(): StyleProposer;
}
