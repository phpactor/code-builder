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
use Microsoft\PhpParser\TextEdit;
use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\Util\LineColFromOffset;

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
        $edits = $this->indentations($node, 0, 1);
        $builder->text(TextEdit::applyEdits($edits, $document->__toString()));

        return $builder->build();
    }

    private function indentations(Node $node, int $level, int $prevLineNb): array
    {
        $lineCol = (new LineColFromOffset)->__invoke(
            $node->getFileContents(),
            $node->getStart()
        );

        $edits = [];

        if ($lineCol->line() !== $prevLineNb && $edit = $this->fixIndentation($node, $level)) {
            $edits[] = $edit;
        }

        if (
            $node instanceof ClassMembersNode ||
            $node instanceof CompoundStatementNode
        ) {
            $level++;
        }

        foreach ($node->getChildNodes() as $childNode) {
            $edits = array_merge($edits, $this->indentations($childNode, $level, $lineCol->line()));
        }

        return $edits;
    }

    private function fixIndentation(Node $node, int $level)
    {
        $existingIndent = $this->indentation($node);
        $indent = str_repeat($this->indent, $level);

        if (strlen($existingIndent) === strlen($indent)) {
            return null;
        }

        return new TextEdit($node->getStart(), 0, $indent);
    }

    private function indentation(Node $node): string
    {
        // TODO: This is an improved version of the one in the other
        // fixer. Move it somewhere else and test it.

        $whitespace = $node->getLeadingCommentAndWhitespaceText();
        // TODO: do not use "\n", can be different on different platforms
        $newLinePos = (int)strrpos($whitespace, "\n");
        return substr($whitespace, $newLinePos + 1);
    }
}
