<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;

class NodeHelper
{
    /**
     * @param Node $node
     * @param QualifiedName|Token $type
     */
    public static function resolvedShortName(Node $node, $type = null): string
    {
        if ($type === null) {
            return '';
        }

        if ($type instanceof Token) {
            return $type->getText($node->getFileContents());
        }

        $resolvedName = $type->getResolvedName();

        if (is_string($resolvedName)) {
            return $resolvedName;
        }

        $parts = $resolvedName->getNameParts();

        if (count($parts) === 0) {
            return '';
        }

        $part = '';

        if (count($parts) == 1) {
            $part = reset($parts);
        }

        if (count($parts) > 1) {
            $part = array_pop($parts);
        }

        if ($part instanceof Token) {
            return $part->getText($type->getFileContents());
        }

        return $part;
    }

    public static function nodesOfTypes(array $types, Node $node, $nodes = []): array
    {
        if (in_array(get_class($node), $types)) {
            $nodes[] = $node;
        }

        foreach ($node->getChildNodes() as $childNode) {
            $nodes = self::nodesOfTypes($types, $childNode, $nodes);
        }

        return $nodes;
    }
}
