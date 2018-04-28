<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Util;

use ArrayIterator;
use IteratorAggregate;
use Microsoft\PhpParser\Node;

class ImportedNames implements IteratorAggregate
{
    private $fullyQualifiedNames = [];

    public function __construct(Node $node)
    {
        $this->fullyQualfiedNamesFromNode($node);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->fullyQualifiedNames);
    }

    private function fullyQualfiedNamesFromNode(Node $node)
    {
        if ('SourceFileNode' == $node->getNodeKindName()) {
            return [];
        }

        $table = $node->getImportTablesForCurrentScope();

        foreach ($table[0] as $shortName => $resolvedName) {
            $this->fullyQualifiedNames[(string) $resolvedName] = (string) $resolvedName;
        }
    }

    public function fullyQualifiedNames()
    {
        return array_values($this->fullyQualifiedNames);
    }
}
