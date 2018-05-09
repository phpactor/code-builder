<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\ResolvedName;
use Microsoft\PhpParser\Token;
use RuntimeException;

class NodeHelper
{
    public static function resolvedShortName(QualifiedName $node = null): string
    {
        if ($node === null) {
            return '';
        }

        $parts = $node->getResolvedName()->getNameParts();

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
            return $part->getText($node->getFileContents());
        }

        return $part;
    }
}
