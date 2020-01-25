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
use Phpactor\CodeBuilder\Util\Line;
use Phpactor\CodeBuilder\Util\Lines;
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
     * @var string
     */
    private $startNodeId;

    private $lineIndentations = [];

    public function __construct(TextFormat $textFormat)
    {
        $this->textFormat = $textFormat;
    }

    public function onEnter(NodeQuery $node): TextEdits
    {
        if (null === $this->startNodeId) {
            $this->startNodeId = $node->id();
        }

        if (
            in_array($node->fqn(), $this->levelChangers)
        ) {
            $this->incLineNumber($node);
        }
            return TextEdits::none();
    }

    public function onExit(NodeQuery $node): TextEdits
    {
        if (
            in_array($node->fqn(), $this->levelChangers)
        ) {
            $this->decLineNumber($node);
        }

        if ($node->id() !== $this->startNodeId) {
            return TextEdits::none();
        }

        return $this->buildIndentationEdits($node->lines(), $this->lineIndentations);
    }

    private function incLineNumber(NodeQuery $node): void
    {
        $this->initLineIndentation($node->fullStartLineNumber());
        $this->lineIndentations[$node->fullStartLineNumber()]++;
    }

    private function decLineNumber(NodeQuery $node): void
    {
        $this->initLineIndentation($node->endLineNumber());
        $this->lineIndentations[$node->endLineNumber()]--;
    }

    private function buildIndentationEdits(Lines $lines, array $lineIndentations): TextEdits
    {
        $textEdits = TextEdits::none();
        $level = 0;
        foreach ($lines as $lineNo => $line) {
            var_dump(token_get_all($line->content()));
            $textEdits = $textEdits->merge($this->lineEdits($level, $lineNo + 1, $line, $lineIndentations));

        }
        return $textEdits;
    }

    private function lineEdits(int &$level, int $lineNo, Line $line, array $lineIndentations): TextEdits
    {
        var_dump($lineIndentations);die();
        $textEdit = new TextEdit(
            $line->start(),
            $line->contentLength(),
            $this->textFormat->indent(ltrim($line->content()), $level)
        );

        if (isset($lineIndentations[$lineNo])) {
            if ($lineIndentations[$lineNo] > 0) {
                $level++;
            }
            if ($lineIndentations[$lineNo] < 0) {
                $level--;
            }
        }

        return TextEdits::one($textEdit);
    }

    private function initLineIndentation(int $lineNo): void
    {
        if (!isset($this->lineIndentations[$lineNo])) {
            $this->lineIndentations[$lineNo] = 0;
        }
    }
}
