<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Microsoft\PhpParser\Node;
use OutOfBoundsException;
use RuntimeException;

class NodeQueries implements IteratorAggregate
{
    /**
     * @var array
     */
    private $nodeQueries;

    public function __construct(NodeQuery ...$nodeQueries)
    {
        $this->nodeQueries = $nodeQueries;
    }

    public function indexOf(NodeQuery $target): int
    {
        foreach ($this->nodeQueries as $index => $query) {
            if ($query->id() === $target->id()) {
                return $index;
            }
        }

        throw new OutOfBoundsException(
            'Could not find node-query "%s" in collection with "%s"',
            get_class($target->id()),
            implode('", "', $this->ids())
        );
    }

    public function ofType(string $fqn): self
    {
        return new self(...array_filter($this->nodeQueries, function (NodeQuery $query) use ($fqn) {
            return $query->fqn() === $fqn;
        }));
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->nodeQueries);
    }

    public static function fromNodes(Node ...$nodes): self
    {
        return new self(...array_map(function (Node $node) {
            return new NodeQuery($node);
        }, $nodes));
    }

    private function ids(): array
    {
        return array_map(function (NodeQuery $node) {
            return $node->id();
        }, $this->nodeQueries);
    }

    public function preceding(NodeQuery $target): self
    {
        $preceding = [];
        foreach ($this->nodeQueries as $query) {
            if ($target->id() === $query->id()) {
                return new self(...$preceding);
            }
            $preceding[] = $query;
        }

        return new self();
    }

    public function count(): int
    {
        return count($this->nodeQueries);
    }

    public function last()
    {
        $query = null;
        foreach ($this->nodeQueries as $query) {
        }
        if (null === $query) {
            throw new RuntimeException(
                'Query collection is empty when trying to get last query'
            );
        }
        return $query;
    }
}
