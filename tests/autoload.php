<?php

use Microsoft\PhpParser\Node;
use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;
use SebastianBergmann\Exporter\Exporter;

require __DIR__  . '/../vendor/autoload.php';

function debug_node($node): void
{
    if ($node instanceof NodeQuery) {
        $node = $node->innerNode();
    }

    if (!$node instanceof Node) {
        throw new RuntimeException(sprintf(
            'Invalid debug node type "%s"', get_class($node)
        ));
    }

    $exporter = new Exporter();
    $lines = [];
    $lines[] = get_class($node);
    $lines[] = sprintf('start: %s, end: %s', $node->getFullStart(), $node->getEndPosition());
    $lines[] = sprintf('%s', $exporter->export($node->getFullText()));

    echo PHP_EOL.implode("\n", $lines).PHP_EOL;
}
