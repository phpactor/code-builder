<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;
use Phpactor\CodeBuilder\Util\TextUtil;
use SebastianBergmann\Exporter\Exporter;

class IndentationProposer implements StyleProposer
{
    private $levelChangers = [
        ClassMembersNode::class,
        CompoundStatementNode::class,
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

    private function editsForIndentation(NodeQuery $node): TextEdits
    {
        // skip if same line number as previous line
        if ($node->lineNumber() === $this->currentLineNumber) {
            return TextEdits::none();
        }

        $this->currentLineNumber = $node->lineNumber();
        $textEdits = $this->replaceIndentation($node);

        return $textEdits;
    }

    public function onExit(NodeQuery $node): TextEdits
    {
        if (in_array($node->fqn(), $this->levelChangers)) {
            $this->level--;
        }

        if ($this->level < 0) {
debug_node($node);
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

    private function replaceIndentation(NodeQuery $node): TextEdits
    {
        $selectionStart = $node->fullStart();
        $selectionEnd = $node->start();

        return $this->indentSelection($node, $selectionStart, $selectionEnd);
    }

    private function replaceWithIndentation(TextEdits $edits, int $selectionStart, int $start, int $length)
    {
        return $edits->add(
            new TextEdit(
                $selectionStart + $start,
                $length,
                $this->textFormat->indentation($this->level)
            )
        );
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
}
