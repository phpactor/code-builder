<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
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
        ClassConstDeclaration::class
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

        // if node is of same type and not the first
        if ($node->siblings()->ofType($node->fqn())->indexOf($node) > 0) {
            return $this->proposeSameSiblingFix($node);
        }

        // if node is first of it's kind
        if ($node->siblings()->ofType($node->fqn())->indexOf($node) === 0) {
            return $this->proposeFirstOfKindFix($node);
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

        if (count($positions) === 1) {
            return TextEdits::none();
        }

        array_shift($positions);
        $first = reset($positions);
        $length = $positions[count($positions) - 1];

        $start = $node->fullStart() + $start + 1;

        return TextEdits::fromTextEdits([
            new TextEdit($start, $length, '')
        ]);
    }

    private function proposeFirstOfKindFix(NodeQuery $node): TextEdits
    {
        if ($node->siblings()->preceding($node)->count() === 0) {
            return $this->removeBlankLines($node);
        }

        $edits = $this->removeBlankLines($node);
        $edits = $edits->add(new TextEdit($node->fullStart(), 0, "\n"));

        return $edits;
    }
}
