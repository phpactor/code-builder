<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Generator;

interface Builder
{
    public static function childNames(): array;

    public function nodes(): Generator;
}
