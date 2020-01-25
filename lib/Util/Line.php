<?php

namespace Phpactor\CodeBuilder\Util;

class Line
{
    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $end;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string|null
     */
    private $newLineChar;

    public function __construct(int $start, int $end, string $content, ?string $newLineChar)
    {
        $this->start = $start;
        $this->end = $end;
        $this->content = $content;
        $this->newLineChar = $newLineChar;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function end(): int
    {
        return $this->end;
    }

    public function newLineChar(): string
    {
        return $this->newLineChar;
    }

    public function start(): int
    {
        return $this->start;
    }

    public function contentEnd(): int
    {
        return $this->start + strlen($this->content);
    }

    public function contentLength(): int
    {
        return $this->contentEnd() - $this->start();
    }
}
