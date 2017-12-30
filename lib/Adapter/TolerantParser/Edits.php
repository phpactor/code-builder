<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;

class Edits
{
    private $edits = [];

    public function remove($node)
    {
        $this->edits[] = new TextEdit($node->getFullStart(), $node->getFullWidth(), '');
    }

    public function after($node, string $text)
    {
        $this->edits[] = new TextEdit($node->getEndPosition(), 0, $text);
    }

    public function replace($node, string $text)
    {
        $this->edits[] = new TextEdit($node->getFullStart(), $node->getFullWidth(), $text);
    }

    public function apply(string $code)
    {
        return trim(TextEdit::applyEdits($this->edits, $code));
    }

    public function add(TextEdit $textEdit)
    {
        $this->edits[] = $textEdit;
    }
}
