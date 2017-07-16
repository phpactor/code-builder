<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Code;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\TextEdit;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Phpactor\CodeBuilder\Domain\Renderer;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Phpactor\CodeBuilder\Domain\Prototype\Type;

class TolerantUpdater implements Updater
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var TextEdit[]
     */
    private $edits = [];

    public function __construct(Renderer $renderer, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->renderer = $renderer;
    }

    public function apply(Prototype $prototype, Code $code): Code
    {
        $this->edits = [];
        $node = $this->parser->parseSourceFile((string) $code);

        $this->updateNamespace($prototype, $node);
        $this->updateUseStatements($prototype, $node);

        foreach ($prototype->classes() as $class) {
            $this->updateClasses($prototype, $node);
        }

        return Code::fromString(trim(TextEdit::applyEdits($this->edits, (string) $code)));
    }

    private function updateNamespace(SourceCode $prototype, SourceFileNode $node)
    {
        $namespaceNode = $node->getFirstChildNode(NamespaceDefinition::class);

        if (null !== $namespaceNode && NamespaceName::root() == $prototype->namespace()) {
            return;
        }

        /** @var $namespaceNode NamespaceDefinition */
        if ($namespaceNode && $namespaceNode->name->getText() == (string) $prototype->namespace()) {
            return;
        }

        if (empty((string) $prototype->namespace())) {
            return;
        }

        if ($namespaceNode) {
            $this->replace($namespaceNode, 'namespace ' . (string) $prototype->namespace() . ';');
            return;
        }

        $startTag = $node->getFirstChildNode(InlineHtml::class);
        $this->after($startTag, 'namespace ' . (string) $prototype->namespace() . ';' . PHP_EOL.PHP_EOL);
    }

    private function updateUseStatements(SourceCode $prototype, SourceFileNode $node)
    {
        if (0 === count($prototype->useStatements())) {
            return;
        }

        $lastNode = $node->getFirstChildNode(NamespaceUseDeclaration::class, NamespaceDefinition::class, InlineHtml::class);

        $this->after($lastNode, PHP_EOL);

        if ($lastNode instanceof NamespaceDefinition) {
            $this->after($lastNode, PHP_EOL);
        }

        /** @var $usePrototype Type */
        foreach ($prototype->useStatements() as $usePrototype) {
            foreach ($node->getChildNodes() as $childNode) {
                if ($childNode instanceof NamespaceUseDeclaration) {
                    foreach ($childNode->useClauses->getElements() as $useClause) {
                        if ($useClause->namespaceName->getText() == $usePrototype->__toString()) {
                            continue 3;
                        }
                    }
                }
            }
            $this->after($lastNode, 'use ' . (string) $usePrototype . ';' . PHP_EOL);
        }
    }

    private function updateClasses()
    {
    }

    private function remove(Node $node)
    {
        $this->edits[] = new TextEdit($node->getFullStart(), $node->getFullWidth(), '');
    }

    private function after(Node $node, string $text)
    {
        $this->edits[] = new TextEdit($node->getEndPosition(), 0, $text);
    }

    private function replace(Node $node, string $text)
    {
        $this->edits[] = new TextEdit($node->getFullStart(), $node->getFullWidth(), $text);
    }
}
