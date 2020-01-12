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
        IfStatementNode::class,
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
        if ($node->lineNumber() === $this->currentLineNumber) {
            return TextEdits::none();
        } else {
            $this->currentLineNumber = $node->lineNumber();
        }

        $textEdits = $this->replaceIndentation($node);

        if (in_array($node->fqn(), $this->levelChangers)) {
            $this->level++;
        }

        return $textEdits;
    }

    public function onExit(NodeQuery $node): void
    {
        if (in_array($node->fqn(), $this->levelChangers)) {
            $this->level--;
        }
    }

    private function replaceIndentation(NodeQuery $node): TextEdits
    {
        $chars = str_split($node->leadingText());
        $prevChar = null;
        $start = $end = $length = 0;
        $edits = TextEdits::none();
        $replaceLast = false;
        $ranges = [];

        foreach ($chars as $pos => $char) {
            if (!$start && $char === $this->textFormat->newLineChar()) {
                $start = $pos + strlen($char);
                continue;
            }

            $length = $pos - $start;

            if ($char != ' ') {
                $ranges[] = [$start, $length];
                $start = null;
            }
        }

        if ($start) {
            $ranges[] = [$start, $pos];
        }


        foreach ($ranges as [$start, $length]) {
            $edits = $this->replaceWithIndentation($edits, $node, $start, $length);
        }

        return $edits;
    }

    private function replaceWithIndentation(TextEdits $edits, NodeQuery $node, int $start, int $length)
    {
        return $edits->add(
            new TextEdit(
                $node->fullStart() + $start,
                $length,
                $this->textFormat->indentation($this->level)
            )
        );
    }
}
