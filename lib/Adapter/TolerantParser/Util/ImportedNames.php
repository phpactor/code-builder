<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Util;

use ArrayIterator;
use IteratorAggregate;
use Microsoft\PhpParser\Node;

class ImportedNames implements IteratorAggregate
{
    /**
     * @var array
     */
    private $table;

    public function __construct(Node $node)
    {
        $this->buildTable($node);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->fullyQualfiedNamesFromNode());
    }

    public function fullyQualifiedNames(): array
    {
        return array_values($this->fullyQualfiedNamesFromNode());
    }

    private function fullyQualfiedNamesFromNode(): array
    {
        $names = [];
        foreach ($this->table[0] as $shortName => $resolvedName) {
            $names[(string) $resolvedName] = (string) $resolvedName;
        }

        return $names;
    }

    private function buildTable(Node $node): void
    {
        if ('SourceFileNode' == $node->getNodeKindName()) {
            $this->table =  [
                [],
                [],
                []
            ];
            return;
        }

        $this->table = $node->getImportTablesForCurrentScope();
    }
}
