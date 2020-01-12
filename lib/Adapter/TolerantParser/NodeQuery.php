<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use SebastianBergmann\Exporter\Exporter;

class NodeQuery
{
    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function fqn(): string
    {
        return get_class($this->node);
    }

    public function siblings(): NodeQueries
    {
        return NodeQueries::fromNodes(
            ...$this->node->getParent()->getChildNodes()
        );
    }

    public function id(): string
    {
        return spl_object_hash($this->node);
    }

    public function leadingText(): string
    {
        return $this->node->getLeadingCommentAndWhitespaceText();
    }

    public function fullStart(): int
    {
        return $this->node->getFullStart();
    }

    public function debug(): void
    {
        $exporter = new Exporter();
        $lines = [];
        $lines[] = get_class($this->node);
        $lines[] = sprintf('start: %s, end: %s', $this->node->getFullStart(), $this->node->getEndPosition());
        $lines[] = sprintf('%s', $exporter->export($this->node->getFullText()));

        echo PHP_EOL.implode("\n", $lines).PHP_EOL;
    }
}
