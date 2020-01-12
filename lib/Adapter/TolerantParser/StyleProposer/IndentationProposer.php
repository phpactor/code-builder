<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer;
use Phpactor\CodeBuilder\Domain\TextEdit;
use Phpactor\CodeBuilder\Domain\TextEdits;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\TolerantParser\NodeQuery;
use Phpactor\CodeBuilder\Util\TextUtil;
use SebastianBergmann\Exporter\Exporter;

class IndentationProposer implements StyleProposer
{
    private $levelChangers = [
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
        return TextEdits::none();
    }
}
