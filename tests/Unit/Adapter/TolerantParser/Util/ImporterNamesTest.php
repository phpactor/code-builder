<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\ImportedNames;

class ImporterNamesTest extends TestCase
{
    public function testReturnsEmptyArrayForSourceFileNode()
    {
        $node = $this->parse(<<<'EOT'
<?php
EOT
        );

        $iterator = new ImportedNames($node);

        $this->assertEquals([], $iterator->fullyQualifiedNames());
    }

    public function testReturnsFullyQualifiedNames()
    {
        $node = $this->parse(<<<'EOT'
<?php

use Foobar;
use Barfoo\Barfoo;

class Foo
{
    }
EOT
        );

        foreach ($node->getDescendantNodes() as $node) {
        }

        $iterator = new ImportedNames($node);
        $this->assertEquals(['Foobar', 'Barfoo\Barfoo'], $iterator->fullyQualifiedNames());
    }

    private function parse($source): Node
    {
        $parser = new Parser();
        return $parser->parseSourceFile($source);
    }

}
