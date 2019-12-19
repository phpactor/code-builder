<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Fixer;

use Microsoft\PhpParser\Node;
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
        $node = $this->parser->parseSourceFile($text);
        $edits = $this->indentations($node);

        return TextEdits::fromTextEdits($edits);
    }

    private function indentations(Node $node): array
    {
        $edits = [];

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
        $edits[] = new TextEdit(
            $node->getFullStart(),
            $node->getStart() - $node->getFullStart(),
            $replace
        );

        foreach ($node->getChildNodes() as $childNode) {
            $edits = array_merge($edits, $this->indentations($childNode));
        }

        return $edits;
    }
}
