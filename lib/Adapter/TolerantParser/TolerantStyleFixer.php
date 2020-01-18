<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\StyleFixer;
use Phpactor\CodeBuilder\Domain\TextEdits;

class TolerantStyleFixer implements StyleFixer
{
    /**
     * @var StyleProposer[]
     */
    private $proposers;

    /**
     * @var Parser
     */
    private $parser;


    public function __construct(Parser $parser = null, StyleProposer ...$proposers)
    {
        $this->proposers = $proposers;
        $this->parser = $parser ?: new Parser();
    }

    public function fix(string $code): string
    {
        foreach ($this->proposers as $proposer) {
            $rootNode = $this->parser->parseSourceFile($code);
            $edits = new TextEdits();
            $edits = $this->walk($proposer, $rootNode, $edits);
            $code = $edits->apply($code);
        }

        return $code;
    }

    private function walk(StyleProposer $proposer, Node $node, TextEdits $edits): TextEdits
    {
        $edits = $edits->merge($proposer->onEnter(new NodeQuery($node)));

        foreach ($node->getChildNodes() as $childNode) {
            $edits = $this->walk($proposer, $childNode, $edits);
        }

        return $edits->merge($proposer->onExit(new NodeQuery($node)));
    }
}
