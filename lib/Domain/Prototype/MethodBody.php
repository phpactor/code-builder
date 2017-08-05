<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\Lines;

final class MethodBody extends Prototype
{
    /**
     * @var Lines
     */
    private $lines;

    private function __construct(Lines $lines = null)
    {
        $this->lines = $lines;
    }

    public static function fromLines(Lines $lines): MethodBody
    {
        return new self($lines);
    }

    public static function empty(): MethodBody
    {
        return new self(Lines::empty());
    }

    public function lines(): Lines
    {
        return $this->lines;
    }
}

