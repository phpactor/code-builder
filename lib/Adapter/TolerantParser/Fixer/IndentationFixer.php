<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\DelimitedList;
use Microsoft\PhpParser\Node\InterfaceMembers;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\TraitMembers;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Util\TextUtil;

class IndentationFixer implements StyleProposer
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var TextFormat
     */
    private $textFormat;


    public function __construct(?Parser $parser = null, ?TextFormat $textFormat = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->textFormat = $textFormat ?: new TextFormat();
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
            $node instanceof DelimitedList ||
            $node instanceof InterfaceMembers ||
            $node instanceof TraitMembers
        ) {
            $level++;
        }

        foreach ($node->getChildNodes() as $childNode) {
            $edits = array_merge($edits, $this->indentations($childNode, $level));
        }

        if ($level > 0) {
            $level--;
        }

        $edits = $this->indent(
            $edits,
            $node,
            $level,
            true
        );

        return $edits;
    }

    private function indent(array $edits, Node $node, int $level, $end = false): array
    {
        $text = $node->getFileContents();

        if ($end) {
            $start = $this->getOffsetForLastChild($node);
            $length = $node->getEndPosition() - $start;
        } else {
            $start = $node->getFullStart();
            $length = $this->getOffsetUntilFirstChild($node) - $start;
        }

        if ($length === 0) {
            return $edits;
        }

        $text = substr($text, $start, $length);

        // if there are no new lines in the selection, return
        if (count(TextUtil::lines($text)) === 1) {
            return $edits;
        }

        $text = $this->textFormat->indentReplace($text, $level);

        $edits[] = new TextEdit($start, $length, $text);

        return $edits;
    }

    private function getOffsetUntilFirstChild(Node $node): int
    {
        foreach ($node->getChildNodes() as $childNode) {
            return $childNode->getFullStart();
        }

        return $node->getEndPosition();
    }

    private function getOffsetForLastChild(Node $node)
    {
        $childNode = $node;
        foreach ($node->getChildNodes() as $childNode) {
        }

        return $childNode->getEndPosition();
    }
}
