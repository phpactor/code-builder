<?php
namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\ClassUpdater;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\InterfaceUpdater;

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

    /**
     * @var TextFormat
     */
    private $textFormat;

    public function __construct(Renderer $renderer, TextFormat $textFormat = null, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->textFormat = $textFormat ?: new TextFormat();
        $this->renderer = $renderer;
        $this->classUpdater = new ClassUpdater($renderer);
        $this->interfaceUpdater = new InterfaceUpdater($renderer);
    }

    public function apply(Prototype $prototype, Code $code): Code
    {
        $edits = new Edits($this->textFormat);
        $node = $this->parser->parseSourceFile((string) $code);

        $this->updateNamespace($edits, $prototype, $node);
        $this->updateUseStatements($edits, $prototype, $node);
        $this->updateClasses($edits, $prototype, $node);

        return Code::fromString($edits->apply((string) $code));
    }

    private function updateNamespace(Edits $edits, SourceCode $prototype, SourceFileNode $node)
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
            $edits->replace($namespaceNode, 'namespace ' . (string) $prototype->namespace() . ';');
            return;
        }

        $startTag = $node->getFirstChildNode(InlineHtml::class);
        $edits->after($startTag, 'namespace ' . (string) $prototype->namespace() . ';' . PHP_EOL.PHP_EOL);
    }

    private function updateUseStatements(Edits $edits, SourceCode $prototype, SourceFileNode $node)
    {
        if (0 === count($prototype->useStatements())) {
            return;
        }

        $lastNode = $node->getFirstChildNode(NamespaceUseDeclaration::class, NamespaceDefinition::class, InlineHtml::class);

        // fast forward to last use declaration
        if ($lastNode instanceof NamespaceUseDeclaration) {
            $parent = $lastNode->parent;
            foreach ($parent->getChildNodes() as $child) {
                if ($child instanceof NamespaceUseDeclaration) {
                    $lastNode = $child;
                }
            }
        }

        if ($lastNode instanceof NamespaceDefinition) {
            $edits->after($lastNode, PHP_EOL);
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

            $newUseStatement = PHP_EOL . 'use ' . (string) $usePrototype . ';';

            $edits->after($lastNode, $newUseStatement);
        }

        if ($lastNode instanceof InlineHtml) {
            $edits->after($lastNode, PHP_EOL . PHP_EOL);
        }
    }

    private function updateClasses(Edits $edits, SourceCode $prototype, SourceFileNode $node)
    {
        $classNodes = [];
        $interfaceNodes = [];
        $lastStatement = null;
        foreach ($node->statementList as $classNode) {
            $lastStatement = $classNode;

            if ($classNode instanceof ClassDeclaration) {
                $name = $classNode->name->getText($node->getFileContents());
                $classNodes[$name] = $classNode;
            }

            if ($classNode instanceof InterfaceDeclaration) {
                $name = $classNode->name->getText($node->getFileContents());
                $interfaceNodes[$name] = $classNode;
            }
        }

        foreach ($prototype->classes()->in(array_keys($classNodes)) as $classPrototype) {
            $this->classUpdater->updateClass($edits, $classPrototype, $classNodes[$classPrototype->name()]);
        }

        foreach ($prototype->interfaces()->in(array_keys($interfaceNodes)) as $classPrototype) {
            $this->interfaceUpdater->updateInterface($edits, $classPrototype, $interfaceNodes[$classPrototype->name()]);
        }

        if (substr($lastStatement->getText(), -1) !== PHP_EOL) {
            $edits->after($lastStatement, PHP_EOL);
        }

        $classes = array_merge(
            iterator_to_array($prototype->classes()->notIn(array_keys($classNodes))),
            iterator_to_array($prototype->interfaces()->notIn(array_keys($interfaceNodes)))
        );

        $index = 0;
        foreach ($classes as $classPrototype) {
            if ($index > 0 && $index + 1 == count($classes)) {
                $edits->after($lastStatement, PHP_EOL);
            }
            $edits->after($lastStatement, PHP_EOL . $this->renderer->render($classPrototype));
            $index++;
        }
    }
}
