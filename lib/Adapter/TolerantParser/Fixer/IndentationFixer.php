<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\DelimitedList;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;

// Algorithm:
//
//
// level = 0
// 1 Indent the area between the node start and the first child
// 2 Indent the area between the last child end and the node end
// 3 If node is structural, level ++
// 4 Iterate goto 1

class IndentationFixer implements StyleFixer
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var string
     */
    private $indent;

    public function __construct(Parser $parser, string $indent = '    ')
    {
        $this->parser = $parser;
        $this->indent = $indent;
    }

    public function propose(string $text): TextEdits
    {
        $node = $this->parser->parseSourceFile($text);
        $edits = $this->indentations($node, 0);

        return TextEdits::fromTextEdits($edits);
    }

    private function indentations(Node $node, int $level): array
    {
        $edits = [];

        $edits = $this->indent(
            $edits,
            $node,
            $level
        );

        if (
            $node instanceof ClassMembersNode ||
            $node instanceof CompoundStatementNode ||
            $node instanceof DelimitedList
        ) {
            $level++;
        }

        foreach ($node->getChildNodes() as $childNode) {
            $edits = array_merge($edits, $this->indentations($childNode, $level));
        }

        return $edits;
    }

    private function indent(array $edits, Node $node, int $level): array
    {
        $text = $node->getFileContents();
        $start = $node->getFullStart();
        $length = $this->getOffsetUntilFirstChild($node) - $start;

        if ($length === 0) {
            return $edits;
        }

        $text = substr($text, $start, $length);

        // if there are no new lines in the selection, return
        if (!preg_match('{\R}m', $text)) {
            return $edits;
        }

        $text = TextFormat::indentationRemove($text);
        $text = TextFormat::indentApply($text, $this->indent, $level);

        $edits[] = new TextEdit($start, $length, $text);

        return $edits;
    }

    private function getOffsetUntilFirstChild(Node $node)
    {
        foreach ($node->getChildNodes() as $childNode) {
            return $childNode->getFullStart();
        }

        return $node->getEndPosition();
    }
}
