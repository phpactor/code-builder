<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\TraitUseClause;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;
use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\Util\LineColFromOffset;
use Phpactor\TextDocument\Util\SplitLines;

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

    public function fix(TextDocument $document): TextDocument
    {
        $builder = TextDocumentBuilder::fromTextDocument($document);

        $node = $this->parser->parseSourceFile($document->__toString());
        $edits = $this->indentations($node, 0);
        $builder->text(TextEdit::applyEdits($edits, $document->__toString()));

        return $builder->build();
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
            $node instanceof CompoundStatementNode
        ) {
            $level++;
        }

        foreach ($node->getChildNodes() as $childNode) {
            $edits = array_merge($edits, $this->indentations($childNode, $level));
        }

        return $edits;
        if ($node->getChildNodes()) {
            $edits = $this->indent(
                $edits,
                $node,
                $level
            );
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

        echo '1^'.$text.'$' . "\n";
        $text = TextFormat::indentationRemove($text);
        $text = TextFormat::indentApply($text, $this->indent, $level);
        echo '2^'.$text.'$' . "\n";

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
