<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;
use Phpactor\CodeBuilder\Util\TextUtil;
use SebastianBergmann\Exporter\Exporter;

class MemberBlankLineProposer implements StyleProposer
{
    private $memberClasses = [
        TraitUseClause::class,
    ];

    /**
     * @var TextFormat
     */
    private $textFormat;

    public function __construct(TextFormat $textFormat)
    {
        $this->textFormat = $textFormat;
    }

    public function propose(NodeQuery $node): TextEdits
    {
        if (!in_array($node->fqn(), $this->memberClasses)) {
            return TextEdits::none();
        }

        if (
            $node->siblings()->ofType($node->fqn())->indexOf($node) > 0
        ) {
            $node->debug();
            return $this->proposeSameSiblingFix($node);
        }

        return TextEdits::none();
    }

    private function proposeSameSiblingFix(NodeQuery $node): TextEdits
    {
        return $this->removeBlankLines($node);
    }

    private function removeBlankLines(NodeQuery $node): TextEdits
    {
        $chars = str_split($node->leadingText());
        $length = null;
        $start = 0;
        $positions = [];

        foreach ($chars as $pos => $char) {
            if ($char === $this->textFormat->newLineChar()) {
                $positions[] = $pos;
                continue;
            }
        }

        array_shift($positions);
        $first = reset($positions);
        $length = $positions[count($positions) - 1];

        $start = $node->fullStart() + $start + 1;

        return TextEdits::fromTextEdits([
            new TextEdit($start, $length, '')
        ]);
    }
}
