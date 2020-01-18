<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Util\TextUtil;

class DocblockIndentationProposer implements StyleProposer
{
    /**
     * @var TextFormat
     */
    private $textFormat;

    public function __construct(TextFormat $textFormat)
    {
        $this->textFormat = $textFormat;
    }

    public function onEnter(NodeQuery $node): TextEdits
    {
        if (!$node->hasDocblock()) {
            return TextEdits::none();
        }

        return TextEdits::one($this->indentationEdits($node));
    }

    private function indentationEdits(NodeQuery $node): TextEdit
    {
        $newLines = [];
        $baseIndent = '';
        $lines = TextUtil::lines($node->leadingText());

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
            $node->fullStart(),
            $node->start() - $node->fullStart(),
            $this->textFormat->implodeLines($newLines)
        );
    }

    public function onExit(NodeQuery $node): TextEdits
    {
        return TextEdits::none();
    }
}
