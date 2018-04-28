<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class UseStatements extends Collection
{
    protected function singularName(): string
    {
        return 'use statement';
    }

    public static function fromUseStatements(array $useStatements)
    {
        return new self($useStatements);
    }

    public function sorted(): UseStatements
    {
        $items = iterator_to_array($this);
        usort($items, function (UseStatement $left, UseStatement $right): int {
            return strcmp((string) $left, $right);
        });

        return new self($items);
    }
}
