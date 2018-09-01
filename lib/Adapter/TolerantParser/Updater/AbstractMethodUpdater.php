<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;
use Phpactor\CodeBuilder\Domain\Prototype\ClassLikePrototype;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\CodeBuilder\Domain\Prototype\ReturnType;

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
        $methodPrototypes = $classPrototype->methods()->in($existingMethodNames);

        $ignoreMethods = [];
        /** @var Method $methodBuilder */
        foreach ($methodPrototypes as $methodPrototype) {

            /** @var MethodDeclaration $methodDeclaration */
            $methodDeclaration = $existingMethods[$methodPrototype->name()];

            if ($methodPrototype->body()->lines()->count()) {
                $bodyNode = $methodDeclaration->compoundStatementOrSemicolon;
                $this->appendLinesToMethod($edits, $methodPrototype, $bodyNode);
            }

            if (false === $methodPrototype->applyUpdate() || $this->prototypeSameAsDeclaration($methodPrototype, $methodDeclaration)) {
                $ignoreMethods[] = $methodPrototype->name();
                continue;
            }

            if ($methodPrototype->applyUpdate()) {
                $this->updateOrAddParameters($edits, $methodPrototype->parameters(), $methodDeclaration);
                $this->updateOrAddReturnType($edits, $methodPrototype->returnType(), $methodDeclaration);
            }
        }

        // Add methods
        $methodPrototypes = $classPrototype->methods()->notIn($existingMethodNames)->notIn($ignoreMethods);

        if (0 === count($methodPrototypes)) {
            return;
        }

        if ($newLine) {
            $edits->after($lastMember, PHP_EOL);
        }

        foreach ($methodPrototypes as $methodPrototype) {
            $edits->after(
                $lastMember,
                PHP_EOL . $edits->indent($this->renderMethod($this->renderer, $methodPrototype), 1)
            );

            if (false === $classPrototype->methods()->isLast($methodPrototype)) {
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

            $existingType = '';
            if ($parameterNode instanceof Parameter) {
                $existingType = $parameter->type() ? NodeHelper::resolvedShortName($parameterNode, $parameterNode->typeDeclaration) : '';
            }

            if ($parameterNode) {
                $parameterNodeName = ltrim($parameterNode->variableName->getText($parameterNode->getFileContents()), '$');

                if ($parameter->type()->notNone() && $parameterNodeName == $parameter->name() && $existingType == (string) $parameter->type()) {
                    continue;
                }

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

    private function updateOrAddReturnType(Edits $edits, ReturnType $returnType, MethodDeclaration $methodDeclaration)
    {
        if (false === $returnType->notNone()) {
            return;
        }

        $returnType = (string) $returnType;
        $existingReturnType = $returnType ? NodeHelper::resolvedShortName($methodDeclaration, $methodDeclaration->returnType) : null;

        if (null === $existingReturnType) {
            // TODO: Add return type
            return;
        }

        if ($returnType === $existingReturnType) {
            return;
        }

        $edits->replace($methodDeclaration->returnType, ' ' . $returnType);
    }

    private function prototypeSameAsDeclaration(Method $methodPrototype, MethodDeclaration $methodDeclaration)
    {
        $parameters = [];
        if (null !== $methodDeclaration->parameters) {
            $parameters = array_filter($methodDeclaration->parameters->children, function ($parameter) {
                return $parameter instanceof Parameter;
            });

            /** @var Parameter $parameter */
            foreach ($parameters as $parameter) {
                $name = ltrim($parameter->variableName->getText($methodDeclaration->getFileContents()), '$');

                // if method prototype doesn't have the existing parameter
                if (false === $methodPrototype->parameters()->has($name)) {
                    return false;
                }

                $parameterPrototype = $methodPrototype->parameters()->get($name);

                $type = $parameterPrototype->type();

                // adding a parameter type
                if (null === $parameter->typeDeclaration && $type->notNone()) {
                    return false;
                }

                // if parameter has a different type
                if (null !== $parameter->typeDeclaration) {
                    $typeName = $parameter->typeDeclaration->getText($methodDeclaration->getFileContents());
                    if ($type->notNone() && (string) $type !== $typeName) {
                        return false;
                    }
                }
            }

        }

        // method prototype has all of the parameters, but does it have extra ones?
        if ($methodPrototype->parameters()->count() !== count($parameters)) {
            return false;
        }

        // are we adding a return type?
        if ($methodPrototype->returnType()->notNone() && null === $methodDeclaration->returnType) {
            return false;
        }

        // is the return type the same?
        if (null !== $methodDeclaration->returnType) {
            $name = $methodDeclaration->returnType->getText();
            if ($methodPrototype->returnType()->__toString() !== $name) {
                return false;
            }
        }

        return true;
    }

    abstract protected function memberDeclarations(ClassLike $classNode);

    abstract protected function memberDeclarationsNode(ClassLike $classNode);

    abstract protected function renderMethod(Renderer $renderer, Method $method);
}
