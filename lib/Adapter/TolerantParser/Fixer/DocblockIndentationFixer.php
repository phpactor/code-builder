<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Util\TextUtil;

class DocblockIndentationFixer implements StyleProposer
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function propose(string $text): TextEdits
    {
        $edits = [];
        $node = $this->parser->parseSourceFile($text);

        foreach ($this->docblockNodes($node) as $docblockNode) {
            $edits[] = $this->indentationEdits($docblockNode);
        }

        return TextEdits::fromTextEdits($edits);
    }

    private function indentationEdits(Node $node): TextEdit
    {
        $newLines = [];
        $baseIndent = '';
        $level = 0;
        $lines = TextUtil::lines($node->getLeadingCommentAndWhitespaceText());

        foreach ($lines as $line) {
            if (preg_match('{^\s*\*}', $line)) {
                $line = TextFormat::indentationRemove($line);
                $line = $baseIndent . TextFormat::indentApply($line, ' ', $level);
            }

            if (preg_match('{^\s*/\*\*}', $line)) {
                $level = 1;
                $baseIndent = TextUtil::lineIndentation($line);
            }

            $newLines[] = $line;
        }

        $replace = implode("\n", $newLines);

        return new TextEdit(
            $node->getFullStart(),
            $node->getStart() - $node->getFullStart(),
            $replace
        );
    }

    private function docblockNodes(Node $node, $nodes = []): array
    {
        if (
            $node instanceof SourceFileNode || 
            $node instanceof ClassDeclaration || 
            $node instanceof MethodDeclaration || 
            $node instanceof PropertyDeclaration
        ) {
            $nodes[] = $node;
        }

        foreach ($node->getChildNodes() as $childNode) {
            $nodes = $this->docblockNodes($childNode, $nodes);
        }

        return $nodes;
    }
}
