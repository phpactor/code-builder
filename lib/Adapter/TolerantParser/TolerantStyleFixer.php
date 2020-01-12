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
        $rootNode = $this->parser->parseSourceFile($code);

        $edits = new TextEdits();
        $edits = $this->walk($rootNode, $edits);

        return $edits->apply($code);
    }

    private function walk(Node $node, TextEdits $edits): TextEdits
    {
        foreach ($this->proposers as $proposer) {
            $edits = $edits->merge($proposer->onEnter(new NodeQuery($node)));
        }

        foreach ($node->getChildNodes() as $childNode) {
            $edits = $this->walk($childNode, $edits);
        }

        foreach ($this->proposers as $proposer) {
            $edits = $edits->merge($proposer->onExit(new NodeQuery($node)));
        }
        return $edits;
    }
}
