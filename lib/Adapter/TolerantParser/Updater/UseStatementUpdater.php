<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\ImportedNames;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;

class UseStatementUpdater
{
    public function updateUseStatements(Edits $edits, SourceCode $prototype, SourceFileNode $node)
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
        $usePrototypes = $this->resolveUseStatements($prototype, $lastNode);

        if (empty($usePrototypes)) {
            return;
        }

        if ($lastNode instanceof NamespaceDefinition) {
            $edits->after($lastNode, PHP_EOL);
        }

        foreach ($usePrototypes as $usePrototype) {
            $editText = $this->buildEditText($usePrototype);

            foreach ($node->getChildNodes() as $childNode) {
                if ($childNode instanceof NamespaceUseDeclaration) {
                    foreach ($childNode->useClauses->getElements() as $useClause) {
                        /* try to find the first lexicographycally greater use
                        statement and insert before if there is one */
                        $cmp = strcmp($useClause->namespaceName->getText(), $usePrototype->__toString());
                        if ($cmp === 0) {
                            continue 3;
                        }
                        if ($cmp > 0) {
                            $edits->before($childNode, $editText . PHP_EOL);
                            continue 3;
                        }
                    }
                }
            }

            $newUseStatement = PHP_EOL . $editText;
            $edits->after($lastNode, $newUseStatement);
        }

        if ($lastNode instanceof InlineHtml) {
            $edits->after($lastNode, PHP_EOL . PHP_EOL);
        }
    }

    private function resolveUseStatements(SourceCode $prototype, Node $lastNode)
    {
        $usePrototypes = $this->filterExisting($lastNode, $prototype);
        $usePrototypes = $this->filterSameNamespace($lastNode, $usePrototypes);

        return $usePrototypes;
    }

    private function filterExisting(Node $lastNode, SourceCode $prototype)
    {
        $existingNames = new ImportedNames($lastNode);
        /** @var UseStatement $usePrototype */
        $usePrototypes = $prototype->useStatements()->sorted();
        $usePrototypes = array_filter(iterator_to_array($usePrototypes), function (UseStatement $usePrototype) use ($existingNames) {
            $existing = $usePrototype->type() === UseStatement::TYPE_FUNCTION ?
                $existingNames->functionNames() :
                $existingNames->classNames();

            $compare = $usePrototype->hasAlias() ? $usePrototype->alias() : $usePrototype->name()->__toString();
            $existing = $usePrototype->hasAlias() ? array_keys($existing) : array_values($existing);

            return false === in_array(
                $compare,
                $existing
            );
        });
        return $usePrototypes;
    }

    private function filterSameNamespace(Node $lastNode, $usePrototypes)
    {
        $sourceNamespace = $lastNode->getNamespaceDefinition()
            ? $lastNode->getNamespaceDefinition()->name->__toString() : null;

        $usePrototypes = array_filter($usePrototypes, function (UseStatement $usePrototype) use ($sourceNamespace) {
            return $sourceNamespace !== $usePrototype->name()->namespace();
        });
        return $usePrototypes;
    }

    private function buildEditText($usePrototype): string
    {
        $editText = [
            'use '
        ];
        if ($usePrototype->type() === UseStatement::TYPE_FUNCTION) {
            $editText[] = 'function ';
        }
        $editText[] = (string) $usePrototype . ';';
        $editText = implode('', $editText);
        return $editText;
    }
}
