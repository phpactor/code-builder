<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class Edits
{
    /**
     * @var array<TextEdit>
     */
    private $edits = [];

    /**
     * @var TextFormat
     */
    private $format;

    public function __construct(TextFormat $format = null)
    {
        $this->format = $format ?: new TextFormat();
        ;
    }

    public function remove($node): void
    {
        $this->edits[] = TextEdit::create($node->getFullStart(), $node->getFullWidth(), '');
    }

    public function before($node, string $text): void
    {
        $this->edits[] = TextEdit::create($node->getStart(), 0, $text);
    }

    public function after($node, string $text): void
    {
        $this->edits[] = TextEdit::create($node->getEndPosition(), 0, $text);
    }

    public function replace($node, string $text): void
    {
        $this->edits[] = TextEdit::create($node->getFullStart(), $node->getFullWidth(), $text);
    }

    public function textEdits(): TextEdits
    {
        return TextEdits::fromTextEdits($this->edits);
    }

    public function add(TextEdit $textEdit): void
    {
        $this->edits[] = $textEdit;
    }

    public function indent(string $string, int $level): string
    {
        return $this->format->indent($string, $level);
    }
}
