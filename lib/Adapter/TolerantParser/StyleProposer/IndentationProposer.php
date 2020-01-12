<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;

class IndentationProposer implements StyleProposer
{
    private $levelChangers = [
        ClassMembersNode::class,
        CompoundStatementNode::class,
        ArrayCreationExpression::class,
        ArgumentExpressionList::class,
    ];

    /**
     * @var TextFormat
     */
    private $textFormat;

    /**
     * @var int
     */
    private $level = 0;

    /**
     * @var int
     */
    private $currentLineNumber = 0;

    public function __construct(TextFormat $textFormat)
    {
        $this->textFormat = $textFormat;
    }

    public function propose(NodeQuery $node): TextEdits
    {
        $edits = $this->editsForIndentation($node);

        if (in_array($node->fqn(), $this->levelChangers)) {
            $this->level++;
        }

        return $edits;
    }

    public function onExit(NodeQuery $node): TextEdits
    {
        if (in_array($node->fqn(), $this->levelChangers)) {
            $this->level--;
        }

        $start = $end = $node->end();

        if ($node->children()->count()) {
            $start = $node->children()->last()->end();
        }

        if ($start === $end) {
            return TextEdits::none();
        }

        return $this->indentSelection($node, $start, $end);
    }

    private function editsForIndentation(NodeQuery $node): TextEdits
    {
        if (false === $node->amTopNodeAtMyPosition()) {
            return TextEdits::none();
        }

        // skip if same line number as previous line
        if ($node->lineNumber() === $this->currentLineNumber) {
            return TextEdits::none();
        }

        $this->currentLineNumber = $node->lineNumber();
        $textEdits = $this->replaceIndentation($node);

        return $textEdits;
    }

    private function replaceIndentation(NodeQuery $node): TextEdits
    {
        $selectionStart = $node->fullStart();
        $selectionEnd = $node->start();

        return $this->indentSelection($node, $selectionStart, $selectionEnd);
    }

    private function indentSelection(NodeQuery $node, int $selectionStart, int $selectionEnd)
    {
        $text = $node->textSelection($selectionStart, $selectionEnd);
        $chars = str_split($text);

        $edits = TextEdits::none();
        $start = $end = $length = 0;
        $ranges = [];
        $parsing = false;

        foreach ($chars as $pos => $char) {
            if (!$parsing && $char === $this->textFormat->newLineChar()) {
                $start = $pos + strlen($char);
                $parsing = true;
                continue;
            }

            $length = $pos - $start;

            // for docblocks
            if ($parsing && $char != ' ') {
                $ranges[] = [$start, $length];
                $parsing = false;
            }
        }

        if ($parsing === true) {
            $ranges[] = [$start, count($chars) - $start];
        }

        foreach ($ranges as [$start, $length]) {
            $edits = $this->replaceWithIndentation($edits, $selectionStart, $start, $length);
        }

        return $edits;
    }

    private function replaceWithIndentation(TextEdits $edits, int $selectionStart, int $start, int $length)
    {
        $indent = $this->textFormat->indentation($this->level);
        return $edits->add(
            new TextEdit(
                $selectionStart + $start,
                $length,
                $indent
            )
        );
    }
}
