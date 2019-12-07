<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\TraitUseClause;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;
use Phpactor\CodeBuilder\Domain\Prototype\ClassLikePrototype;
use Phpactor\CodeBuilder\Domain\Renderer;

abstract class ClassLikeUpdater
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var ClassMethodUpdater
     */
    protected $methodUpdater;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->methodUpdater = new ClassMethodUpdater($renderer);
    }

    protected function resolvePropertyName(Node $property)
    {
        if ($property instanceof Variable) {
            return $property->getName();
        }

        if ($property instanceof AssignmentExpression) {
            return $this->resolvePropertyName($property->leftOperand);
        }

        throw new \InvalidArgumentException(sprintf(
            'Do not know how to resolve property element of type "%s"',
            get_class($property)
        ));
    }

    abstract protected function memberDeclarations(Node $node): array;

    protected function updateProperties(Edits $edits, ClassLikePrototype $classPrototype, Node $classMembers)
    {
        if (count($classPrototype->properties()) === 0) {
            return;
        }

        $previousMember = $classMembers->openBrace;
        $memberDeclarations = $this->memberDeclarations($classMembers);

        $nextMember = reset($memberDeclarations) ?: null;
        $existingPropertyNames = [];

        foreach ($memberDeclarations as $memberNode) {
            // Property goes after traits and constants
            if ($memberNode instanceof TraitUseClause ||
                $memberNode instanceof ClassConstDeclaration
            ) {
                $previousMember = $memberNode;
            }

            if ($memberNode instanceof PropertyDeclaration) {
                foreach ($memberNode->propertyElements->getElements() as $property) {
                    $existingPropertyNames[] = $this->resolvePropertyName($property);
                }
                $previousMember = $memberNode;
                $nextMember = next($memberDeclarations) ?: $nextMember;
                prev($memberDeclarations);
            }
        }

        // If the previous member is neither the open brace of class nor a property
        // Then we must add a blank line before the new properties
        if ($previousMember !== $classMembers->openBrace
            && !($previousMember instanceof PropertyDeclaration)
        ) {
            $edits->after($previousMember, PHP_EOL);
        }

        foreach ($classPrototype->properties()->notIn($existingPropertyNames) as $property) {
            $renderedProperty = $this->renderer->render($property);

            $edits->after($previousMember, PHP_EOL . $edits->indent($renderedProperty, 1));
        }

        if ($previousMember === $classMembers->openBrace &&
            $nextMember instanceof MethodDeclaration
        ) {
            $edits->after($previousMember, PHP_EOL);
        }
    }
}
