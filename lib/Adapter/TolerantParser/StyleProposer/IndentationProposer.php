<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\DelimitedList\ParameterDeclarationList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\InterfaceMembers;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\TraitMembers;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;

class IndentationProposer implements StyleProposer
{
    private $levelChangers = [
        ClassMembersNode::class,
        TraitMembers::class,
        InterfaceMembers::class,
        ParameterDeclarationList::class,
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
    private array $lineHasIndented = [];
    private array $indentNodes = [];

    public function __construct(TextFormat $textFormat)
    {
        $this->textFormat = $textFormat;
    }

    public function onEnter(NodeQuery $node): TextEdits
    {
        $edits = $this->editsForIndentation($node);

        if (in_array($node->fqn(), $this->levelChangers) && !isset($this->lineHasIndented[$node->lineNumber()])) {
            $this->lineHasIndented[$node->lineNumber()] = true;
            $this->indentNodes[] = $node->id();
            $this->level++;
        }

        return $edits;
    }

    public function onExit(NodeQuery $node): TextEdits
    {
        if (in_array($node->id(), $this->indentNodes)) {
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
                $empty = $char === $this->textFormat->newLineChar();
                $ranges[] = [$start, $length, $empty];
                $parsing = false;
            }
        }

        if ($parsing === true) {
            $ranges[] = [$start, count($chars) - $start, false];
        }

        foreach ($ranges as [$start, $length, $empty]) {
            $edits = $this->replaceWithIndentation($edits, $selectionStart, $start, $length, $empty ? 0 : $this->level);
        }

        return $edits;
    }

    private function replaceWithIndentation(TextEdits $edits, int $selectionStart, int $start, int $length, int $level)
    {
        $indent = $this->textFormat->indentation($level);
        return $edits->add(
            new TextEdit(
                $selectionStart + $start,
                $length,
                $indent
            )
        );
    }
}
