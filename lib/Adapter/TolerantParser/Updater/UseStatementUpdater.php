<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\ImportedNames;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;

class UseStatementUpdater
{
    public function updateUseStatements(Edits $edits, SourceCode $prototype, SourceFileNode $node)
    {
        if (0 === count($prototype->useStatements())) {
            return;
        }

        $startNode = $node;
        foreach ($node->getChildNodes() as $childNode) {
            if ($childNode instanceof InlineHtml) {
                $startNode = $node->getFirstChildNode(InlineHtml::class);
            }
            if ($childNode instanceof NamespaceDefinition) {
                $startNode = $childNode;
            }
            if ($childNode instanceof NamespaceUseDeclaration) {
                $startNode = $childNode;
            }
        }

        $bodyNode = null;
        foreach ($node->getChildNodes() as $childNode) {
            if ($childNode->getStart() > $startNode->getStart()) {
                $bodyNode = $childNode;
                break;
            }
        }

        $usePrototypes = $this->resolveUseStatements($prototype, $startNode);

        if (empty($usePrototypes)) {
            return;
        }

        if ($startNode instanceof NamespaceDefinition) {
            $edits->after($startNode, PHP_EOL);
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
            $edits->after($startNode, $newUseStatement);
        }

        if ($startNode instanceof InlineHtml) {
            $edits->after($startNode, "\n");
        }

        if ($bodyNode && NodeHelper::emptyLinesPrecedingNode($bodyNode) === 0) {
            $edits->after($startNode, "\n");
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

            $candidate = $usePrototype->hasAlias() ? $usePrototype->alias() : $usePrototype->name()->__toString();

            // when we are dealing with aliases, they are stored in the array
            // keys...
            $existing = $usePrototype->hasAlias() ? array_keys($existing) : array_values($existing);

            return false === in_array(
                $candidate,
                $existing,
                true
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
