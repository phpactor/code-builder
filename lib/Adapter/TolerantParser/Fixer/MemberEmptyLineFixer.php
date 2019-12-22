<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\CodeBuilder\Domain\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Util\TextUtil;

class MemberEmptyLineFixer implements StyleProposer
{
    private const META_SUCCESSOR = 'successor';
    private const META_NODE_CLASS = 'class';
    private const META_FIRST = 'first';
    private const META_PRECEDING_BLANK_LINES = 'preceding_blank_lines';
    private const META_PRECEDING_BLANK_START = 'blank_start';
    private const META_PRECEDING_BLANK_LENGTH = 'blank_length';
    private const META_INDENTATION = 'indentation';
    private const META_IS_METHOD = 'is_method';
    private const META_HAS_DOCBLOCK = 'has_docblock';

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

        $membersMeta = $this->gatherMetadata($node, [
            TraitUseClause::class,
            ClassConstDeclaration::class,
            PropertyDeclaration::class,
            MethodDeclaration::class,
        ]);

        return TextEdits::fromTextEdits($this->textEdits($membersMeta));
    }

    private function gatherMetadata(Node $node, array $nodeTypes): array
    {
        $nodesMeta = [];
        $nodes = [];

        $previousNodeClass = null;
        foreach (NodeHelper::nodesOfTypes($nodeTypes, $node) as $node) {
            assert($node instanceof Node);
            $meta = [
                self::META_SUCCESSOR => false,
                self::META_NODE_CLASS => get_class($node),
                self::META_FIRST => false,
                self::META_PRECEDING_BLANK_LINES => $this->countBlankLines($node->getLeadingCommentAndWhitespaceText()),
                self::META_PRECEDING_BLANK_START => $node->getFullStart(),
                self::META_PRECEDING_BLANK_LENGTH => $this->blankLength($node),
                self::META_INDENTATION => $this->indentation($node),
                self::META_IS_METHOD => $node instanceof MethodDeclaration,
                self::META_HAS_DOCBLOCK => (bool)$node->getDocCommentText(),
            ];

            if (null === $previousNodeClass) {
                $meta[self::META_FIRST] = true;
            }

            if ($previousNodeClass && $previousNodeClass === $meta[self::META_NODE_CLASS]) {
                $meta[self::META_SUCCESSOR] = true;
            }

            $nodesMeta[] = $meta;
            $previousNodeClass = $meta[self::META_NODE_CLASS];
        }

        return $nodesMeta;
    }

    private function textEdits(array $membersMeta): array
    {
        $edits = [];

        foreach ($membersMeta as $meta) {
            if ($meta[self::META_FIRST] && $meta[self::META_PRECEDING_BLANK_LINES] > 2) {
                $edits = $this->removeBlankLines($edits, $meta);
                continue;
            }

            if (
                !$meta[self::META_IS_METHOD] &&
                (
                    $meta[self::META_SUCCESSOR] &&
                    !$meta[self::META_HAS_DOCBLOCK] &&
                    $meta[self::META_PRECEDING_BLANK_LINES] > 2
                )
            ) {
                $edits = $this->removeBlankLines($edits, $meta);
                continue;
            }

            if (
                !$meta[self::META_FIRST]  &&
                (
                    $meta[self::META_IS_METHOD] ||
                    !$meta[self::META_SUCCESSOR] ||
                    $meta[self::META_HAS_DOCBLOCK]
                ) &&
                $meta[self::META_PRECEDING_BLANK_LINES] == 2
            ) {
                $edits = $this->addBlankLine($edits, $meta);
                continue;
            }
        }

        return $edits;
    }

    private function countBlankLines(string $string): int
    {
        return count(array_filter(
            TextUtil::lines($string),
            function (string $line) {
                return trim($line) === '';
            }
        ));
    }

    private function indentation(Node $node): int
    {
        $whitespace = $node->getLeadingCommentAndWhitespaceText();

        return strlen(substr(
            $whitespace,
            TextUtil::lastNewLineOffset($whitespace)
        ));
    }

    private function removeBlankLines(array $edits, $meta): array
    {
        $edits[] = new TextEdit(
            $meta[self::META_PRECEDING_BLANK_START],
            $meta[self::META_PRECEDING_BLANK_LENGTH],
            $this->textFormat->newLineChar() . str_repeat(' ', $meta[self::META_INDENTATION] - 1)
        );

        return $edits;
    }

    private function addBlankLine($edits, $meta): array
    {
        $edits[] = new TextEdit(
            $meta[self::META_PRECEDING_BLANK_START],
            0,
            $this->textFormat->newLineChar()
        );

        return $edits;
    }

    private function blankLength(Node $node): int
    {
        return strlen(TextUtil::leadingSpace($node->getLeadingCommentAndWhitespaceText()));
    }
}
