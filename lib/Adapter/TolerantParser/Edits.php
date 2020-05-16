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

    public function remove($node)
    {
        $this->edits[] = TextEdit::create($node->getFullStart(), $node->getFullWidth(), '');
    }

    public function before($node, string $text)
    {
        $this->edits[] = TextEdit::create($node->getStart(), 0, $text);
    }

    public function after($node, string $text)
    {
        $this->edits[] = TextEdit::create($node->getEndPosition(), 0, $text);
    }

    public function replace($node, string $text)
    {
        $this->edits[] = TextEdit::create($node->getFullStart(), $node->getFullWidth(), $text);
    }

    public function apply(string $code): string
    {
        return trim(TextEdit::applyEdits($this->edits, $code));
    }

    public function textEdits(): TextEdits
    {
        return TextEdits::fromTextEdits($this->edits);
    }

    public function add(TextEdit $textEdit)
    {
        $this->edits[] = $textEdit;
    }

    public function indent(string $string, int $level): string
    {
        return $this->format->indent($string, $level);
    }
}
