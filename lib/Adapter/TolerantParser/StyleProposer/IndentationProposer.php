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
use Phpactor\CodeBuilder\Util\TextUtil;

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
        return TextEdits::none();
    }

    public function onExit(NodeQuery $node): TextEdits
    {
        if ($node->id() !== $this->startNodeId) {
            return TextEdits::none();
        }

        $tokens = token_get_all($node->fullText());
        $indentations = [];
        $lineNo = 1;

        foreach ($tokens as $token) {
            $content = is_array($token) ? $token[1] : $token;

            if ($newLinesCount = preg_match_all('{(\r\n|\n|\r)}', $content, $matches)) {
                $lineNo += $newLinesCount;
            }

            if (!isset($indentations[$lineNo])) {
                $indentations[$lineNo] = 0;
            }

            if (in_array($content, ['(', '[', '{'])) {
                $indentations[$lineNo]++;
            }

            if (in_array($content, [')', ']', '}'])) {
                $indentations[$lineNo]--;
            }
        }

        return $this->buildIndentEdits($node->lines(), $indentations);
    }

    private function buildIndentEdits(Lines $lines, array $indentations): TextEdits
    {
        $level = 0;
        $edits = TextEdits::none();
        foreach ($lines as $lineOffset => $line) {
            $lineNumber = $lineOffset + 1;
            assert($line instanceof Line);

            $delta = $indentations[$lineNumber] ?? 0;

            if ($delta < 0) {
                $level--;
            }

            $edits = $edits->add(new TextEdit(
                $line->start(),
                $line->contentLength(),
                $this->textFormat->indent(ltrim($line->content()), strlen(ltrim($line->content())) > 0 ? $level : 0)
            ));

            if ($delta > 0) {
                $level++;
            }
        }

        return $edits;
    }
}
