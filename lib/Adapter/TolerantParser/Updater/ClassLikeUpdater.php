<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Renderer;

abstract class ClassLikeUpdater
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var MethodUpdater
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
            'Do not know how to resolve property elemnt of type "%s"',
            get_class($property)
        ));
    }

    protected function updatePrototypeConstants($classPrototype, $existingConstantNames, $lastConstant, $edits, $nextMember)
    {
        foreach ($classPrototype->constants()->notIn($existingConstantNames) as $constant) {
            // if constant type exists then the last constant has a docblock - add a line break
            if ($lastConstant instanceof ConstantDeclaration && $constant->type() != Type::none()) {
                $edits->after($lastConstant, PHP_EOL);
            }

            $edits->after(
                $lastConstant,
                PHP_EOL . $edits->indent($this->renderer->render($constant), 1)
            );

            if ($classPrototype->constants()->isLast($constant) && (
                $nextMember instanceof MethodDeclaration ||
                $nextMember instanceof PropertyDeclaration
            )) {
                $edits->after($lastConstant, PHP_EOL);
            }
        }
    }
}
