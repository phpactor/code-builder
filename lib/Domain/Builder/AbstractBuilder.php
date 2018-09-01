<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Generator;
use RuntimeException;

abstract class AbstractBuilder implements Builder
{
    public function nodes(): Generator
    {
        yield $this;

        foreach (static::childNames() as $childName) {
            $children = (array) $this->$childName;

            foreach ($children as $child) {
                if (!$child instanceof Builder) {
                    throw new RuntimeException(sprintf(
                        'Child "%s" is not a builder instance, it is a "%s"',
                        $childName, is_object($child) ? get_class($child) : gettype($child)
                    ));
                }

                yield from $child->nodes();
            }

        }
    }
}
