<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\CodeBuilder\Util\TextUtil;
use SebastianBergmann\Exporter\Exporter;

class NodeQuery
{
    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function fqn(): string
    {
        return get_class($this->node);
    }

    public function siblings(): NodeQueries
    {
        return NodeQueries::fromNodes(
            ...$this->node->getParent()->getChildNodes()
        );
    }

    public function id(): string
    {
        return spl_object_hash($this->node);
    }

    public function leadingText(): string
    {
        return $this->node->getLeadingCommentAndWhitespaceText();
    }

    public function fullStart(): int
    {
        return $this->node->getFullStart();
    }

    public function innerNode(): Node
    {
        return $this->node;
    }

    public function isMethodDeclaration(): bool
    {
        return $this->fqn() === MethodDeclaration::class;
    }

    public function hasDocblock(): bool
    {
        return !empty($this->node->getDocCommentText());
    }

    public function lineNumber(): int
    {
        return count(TextUtil::lines(substr($this->node->getFileContents(), 0, $this->node->getStart())));
    }

    public function start(): int
    {
        return $this->node->getStart();
    }

    public function end(): int
    {
        return $this->node->getEndPosition();
    }

    public function textSelection(int $selectionStart, int $selectionEnd): string
    {
        return substr($this->node->getFileContents(), $selectionStart, $selectionEnd - $selectionStart);
    }

    public function children(): NodeQueries
    {
        return NodeQueries::fromNodes(...$this->node->getChildNodes());
    }
}
