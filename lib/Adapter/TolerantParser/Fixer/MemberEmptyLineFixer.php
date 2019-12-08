<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\TextEdit;
use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;

class MemberEmptyLineFixer implements StyleFixer
{
    private const META_SUCCESSOR = 'successor';
    private const META_NODE_CLASS = 'class';
    const META_FIRST = 'first';
    const META_PRECEDING_BLANK_LINES = 'preceding_blank_lines';
    const META_PRECEDING_BLANK_START = 'blank_start';
    const META_PRECEDING_BLANK_LENGTH = 'blank_length';
    const META_INDENTATION = 'indentation';


    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function fix(TextDocument $document): TextDocument
    {
        $builder = TextDocumentBuilder::fromTextDocument($document);

        $node = $this->parser->parseSourceFile($document->__toString());

        $membersMeta = $this->gatherMetadata($node, [
            TraitUseClause::class,
            ClassConstDeclaration::class,
            PropertyDeclaration::class,
            MethodDeclaration::class,
        ]);
        $this->updateDocument($builder, $membersMeta);

        return $builder->build();
    }

    private function gatherMetadata(Node $node, array $nodeTypes): array
    {
        $nodesMeta = [];
        $nodes = [];

        foreach ($node->getDescendantNodes() as $decendantNode) {
            if (!in_array(get_class($decendantNode), $nodeTypes)) {
                continue;
            }

            $nodes[] = $decendantNode;
        }

        $previousNode = null;
        foreach ($nodes as $node) {
            assert($node instanceof Node);
            $meta = [
                self::META_SUCCESSOR => false,
                self::META_NODE_CLASS => get_class($node),
                self::META_FIRST => false,
                self::META_PRECEDING_BLANK_LINES => $this->blankLines($node->getLeadingCommentAndWhitespaceText()),
                self::META_PRECEDING_BLANK_START => $node->getFullStart(),
                self::META_PRECEDING_BLANK_LENGTH => $node->getStart() - $node->getFullStart(),
                self::META_INDENTATION => $this->indentation($node),
            ];

            if (null === $previousNode) {
                $meta[self::META_FIRST] = true;
            }

            if ($previousNode && $previousNode === $meta[self::META_NODE_CLASS]) {
                $meta[self::META_SUCCESSOR] = true;
            }

            $nodesMeta[] = $meta;
            $previousNode = $meta[self::META_NODE_CLASS];
        }

        return $nodesMeta;
    }

    private function updateDocument(TextDocumentBuilder $document, array $membersMeta): TextDocumentBuilder
    {
        $edits = [];

        foreach ($membersMeta as $meta) {
            if ($meta[self::META_FIRST] && $meta[self::META_PRECEDING_BLANK_LINES] > 2) {
                $edits = $this->removeBlankLines($edits, $meta);
                continue;
            }

            if (
                $meta[self::META_SUCCESSOR] && 
                $meta[self::META_PRECEDING_BLANK_LINES] > 2
            ) {
                $edits = $this->removeBlankLines($edits, $meta);
                continue;
            }

            if (
                $meta[self::META_FIRST] === false  &&
                $meta[self::META_SUCCESSOR] === false && 
                $meta[self::META_PRECEDING_BLANK_LINES] == 2
            ) {
                $edits = $this->addBlankLine($edits, $meta);
                continue;
            }
        }

        return $document->text(TextEdit::applyEdits($edits, $document->build()->__toString()));

    }

    private function blankLines(string $string): int
    {
        // TODO: Extract this to a tested utility (perhaps stick it in
        //       text-document package)
        return count(array_filter(
            preg_split("{(\r\n|\n|\r)}", $string),
            function (string $line) {
                return trim($line) === '';
            }
        ));
    }

    private function indentation(Node $node)
    {
        $whitespace = $node->getLeadingCommentAndWhitespaceText();
        $newLinePos = strrpos($whitespace, "\n");
        return strlen(substr($whitespace, $newLinePos));
    }

    private function removeBlankLines(array $edits, $meta)
    {
        $edits[] = new TextEdit(
            $meta[self::META_PRECEDING_BLANK_START],
            $meta[self::META_PRECEDING_BLANK_LENGTH],
            PHP_EOL . str_repeat(' ', $meta[self::META_INDENTATION] - 1),
        );

        return $edits;
    }

    private function addBlankLine($edits, $meta)
    {
        $edits[] = new TextEdit(
            $meta[self::META_PRECEDING_BLANK_START],
            0,
            PHP_EOL,
        );

        return $edits;
    }
}
