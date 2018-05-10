<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\ResolvedName;
use Microsoft\PhpParser\Token;
use RuntimeException;

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

        $parts = $type->getResolvedName()->getNameParts();

        if (count($parts) === 0) {
            return '';
        }

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
}
