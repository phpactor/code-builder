<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;
use Phpactor\CodeBuilder\Domain\Prototype\ClassLikePrototype;
use Microsoft\PhpParser\ClassLike;

abstract class AbstractMethodUpdater
{
    /**
     * @var Renderer
     */
    private $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function updateMethods(Edits $edits, ClassLikePrototype $classPrototype, ClassLike $classNode)
    {
        if (count($classPrototype->methods()) === 0) {
            return;
        }

        $lastMember = $this->memberDeclarationsNode($classNode)->openBrace;
        $newLine = false;
        $memberDeclarations = $this->memberDeclarations($classNode);
        $existingMethodNames = [];
        $existingMethods = [];
        foreach ($memberDeclarations as $memberNode) {
            if ($memberNode instanceof PropertyDeclaration) {
                $lastMember = $memberNode;
                $newLine = true;
            }

            if ($memberNode instanceof MethodDeclaration) {
                $lastMember = $memberNode;
                $existingMethodNames[] = $memberNode->getName();
                $existingMethods[$memberNode->getName()] = $memberNode;
                $newLine = true;
            }
        }

        // Update methods
        $methods = $classPrototype->methods()->in($existingMethodNames);

        /** @var Method $method */
        foreach ($methods as $method) {
            /** @var MethodDeclaration $methodDeclaration */
            $methodDeclaration = $existingMethods[$method->name()];
            $bodyNode = $methodDeclaration->compoundStatementOrSemicolon;

            if ($method->body()->lines()->count()) {
                $this->appendLinesToMethod($edits, $method, $bodyNode);
            }

            $this->updateOrAddParameters($edits, $method->parameters(), $methodDeclaration);
        }

        $methods = $classPrototype->methods()->notIn($existingMethodNames);

        if (0 === count($methods)) {
            return;
        }

        if ($newLine) {
            $edits->after($lastMember, PHP_EOL);
        }

        // Add methods
        foreach ($methods as $method) {
            $edits->after(
                $lastMember,
                PHP_EOL . $edits->indent($this->renderMethod($this->renderer, $method), 1)
            );

            if (false === $classPrototype->methods()->isLast($method)) {
                $edits->after($lastMember, PHP_EOL);
            }
        }
    }

    private function appendLinesToMethod(Edits $edits, Method $method, Node $bodyNode)
    {
        if (false === $bodyNode instanceof CompoundStatementNode) {
            return;
        }

        $lastStatement = end($bodyNode->statements) ?: $bodyNode->openBrace;

        foreach ($method->body()->lines() as $line) {
            // do not add duplicate lines
            $bodyNodeLines = explode(PHP_EOL, $bodyNode->getText());

            foreach ($bodyNodeLines as $bodyNodeLine) {
                if (trim($bodyNodeLine) == trim((string) $line)) {
                    continue 2;
                }
            }

            $edits->after(
                $lastStatement,
                PHP_EOL . $edits->indent((string) $line, 2)
            );
        }
    }

    private function updateOrAddParameters(Edits $edits, Parameters $parameters, MethodDeclaration $methodDeclaration)
    {
        if (0 === $parameters->count()) {
            return;
        }

        $parameterNodes = [];
        if ($methodDeclaration->parameters) {
            $parameterNodes = iterator_to_array($methodDeclaration->parameters->getElements());
        }
        $replacementParameters = [];

        foreach ($parameters as $parameter) {
            $parameterNode = current($parameterNodes);

            if ($parameterNode) {
                $parameterNodeName = ltrim($parameterNode->variableName->getText($parameterNode->getFileContents()), '$');

                if ($parameterNodeName == $parameter->name()) {
                    $replacementParameters[] = $this->renderer->render($parameter);
                    array_shift($parameterNodes);
                    continue;
                }
            }

            $replacementParameters[] = $this->renderer->render($parameter);
        }

        foreach ($parameterNodes as $parameterNode) {
            $replacementParameters[] = $parameterNode->getText($parameterNode->getFileContents());
        }

        if ($methodDeclaration->parameters) {
            $edits->replace($methodDeclaration->parameters, implode(', ', $replacementParameters));
            return;
        }

        $edits->add(new TextEdit($methodDeclaration->openParen->getStartPosition() + 1, 0, implode(', ', $replacementParameters)));
    }

    abstract protected function memberDeclarations(ClassLike $classNode);

    abstract protected function memberDeclarationsNode(ClassLike $classNode);

    abstract protected function renderMethod(Renderer $renderer, Method $method);
}
