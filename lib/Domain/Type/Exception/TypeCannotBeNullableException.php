<?php

namespace Phpactor\CodeBuilder\Domain\Type\Exception;

use InvalidArgumentException;
use Phpactor\CodeBuilder\Domain\Prototype\Type;

class TypeCannotBeNullableException extends InvalidArgumentException
{
    public function __construct(Type $type)
    {
        parent::__construct(sprintf(
            'Type %s is not allowed to be nullable',
            (string) $type
        ));
    }
}
