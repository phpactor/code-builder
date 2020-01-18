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

    /**
     * @var int
     */
    private $tolerance;

    public function __construct(array $proposers = [], Parser $parser = null, int $tolerance = 80)
    {
        $this->proposers = $proposers;
        $this->parser = $parser ?: new Parser();
        $this->tolerance = $tolerance;
    }

    public function fix(string $code): string
    {
        foreach ($this->proposers as $proposer) {
            $code = $this->walk(
                $proposer,
                $this->parser->parseSourceFile($code),
                new TextEdits()
            )->apply($code);
        }

        return $code;
    }

    public function fixIntersection(TextEdits $intersection, string $code): string
    {
        foreach ($this->proposers as $proposer) {
            $code = $this->walk(
                $proposer,
                $this->parser->parseSourceFile($code),
                new TextEdits()
            )->intersection($intersection, $this->tolerance)->apply($code);
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
