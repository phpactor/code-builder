<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser\Fixer;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\MemberWhitespaceFixer;
use Phpactor\TestUtils\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;

class MemberWhitespaceFixerTest extends TestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    protected function setUp(): void
    {
        $this->workspace = Workspace::create(__DIR__ . '/../../../Workspace');
        $this->workspace->reset();
    }

    /**
     * @dataProvider provideFixer
     */
    public function testFixer(string $path)
    {
        $this->workspace->loadManifest(file_get_contents($path));
        $document = TextDocumentBuilder::create($this->workspace->getContents('source.php'))->build();
        $fixed = (new MemberWhitespaceFixer(new Parser()))->fix($document);

        self::assertEquals(trim($this->workspace->getContents('expected.php')), trim($fixed->__toString()));
    }

    public function provideFixer(): Generator
    {
        foreach (glob(__DIR__ . '/examples/*.test.php') as $filename) {
            yield basename($filename) => [
                $filename
            ];
        }
    }
}
