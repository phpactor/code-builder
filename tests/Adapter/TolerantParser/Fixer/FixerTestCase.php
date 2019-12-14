<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser\Fixer;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer\MemberEmptyLineFixer;
use Phpactor\CodeBuilder\Domain\StyleFixer;
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
     * @dataProvider provideFixer
     */
    public function testFixer(string $path)
    {
        $this->workspace()->loadManifest(file_get_contents($path));
        $document = TextDocumentBuilder::create($this->workspace()->getContents('source.php'))->build();
        $fixed = $this->createFixer()->fix($document);
        die($fixed);

        self::assertEquals(trim($this->workspace()->getContents('expected.php')), trim($fixed->__toString()));
    }

    public function provideFixer(): Generator
    {
        $name = basename(str_replace('\\', '/', get_class($this->createFixer())));
        $dir = __DIR__ . '/examples/' . $name;

        if (!file_exists($dir)) {
            throw new RuntimeException(sprintf(
                'Directory "%s" does not exist', $dir
            ));
        }


        foreach (glob($dir . '/*.test.php') as $filename) {
            yield basename($filename) => [
                $filename
            ];
        }
    }

    abstract protected function createFixer(): StyleFixer;
}
