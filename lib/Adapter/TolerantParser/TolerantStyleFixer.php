<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
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
        foreach ($this->allNodes($rootNode) as $node) {
            foreach ($this->proposers as $proposer) {
                $edits = $edits->merge($proposer->propose(new NodeQuery($node)));
            }
        }

        return $edits->apply($code);
    }

    private function allNodes(Node $node): Generator
    {
        yield $node;
        foreach ($node->getChildNodes() as $childNode) {
            yield from $this->allNodes($childNode);
        }
    }
}
