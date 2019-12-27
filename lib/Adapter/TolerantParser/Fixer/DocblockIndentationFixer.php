<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
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
        $lines = TextUtil::lines($node->getLeadingCommentAndWhitespaceText());

        foreach ($lines as $line) {
            if (TextUtil::hasDocblock($line)) {
                $line = $this->textFormat->indentRemove($line);
                $line = $baseIndent .' '. $line;
            }

            if (preg_match('{^\s*/\*\*}', $line)) {
                $baseIndent = TextUtil::lineIndentation($line);
            }

            $newLines[] = $line;
        }

        return new TextEdit(
            $node->getFullStart(),
            $node->getStart() - $node->getFullStart(),
            $this->textFormat->implodeLines($newLines)
        );
    }

    private function docblockNodes(Node $node, $nodes = []): array
    {
        return NodeHelper::nodesOfTypes([
            SourceFileNode::class,
            ClassDeclaration::class,
            MethodDeclaration::class,
            PropertyDeclaration::class,
        ], $node);
    }
}
