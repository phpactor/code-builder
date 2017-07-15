<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Classes;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatements;
use Phpactor\CodeBuilder\Domain\Prototype\Interfaces;

class SourceCodeBuilder
{
    /**
     * @var NamespaceName
     */
    private $namespace;

    /**
     * @var Type[]
     */
    private $useStatements = [];

    /**
     * @var ClassBuilder[]
     */
    private $classes = [];

    /**
     * @var InterfaceBuilder[]
     */
    private $interfaces = [];

    public static function create()
    {
        return new self();
    }

    public function namespace(string $namespace): SourceCodeBuilder
    {
        $this->namespace = NamespaceName::fromString($namespace);

        return $this;
    }

    public function use(string $use): SourceCodeBuilder
    {
        $this->useStatements[] = Type::fromString($use);

        return $this;
    }

    public function class(string $name): ClassBuilder
    {
        $this->classes[] = $builder = new ClassBuilder($this, $name);

        return $builder;
    }

    public function interface(string $name): InterfaceBuilder
    {
        $this->interfaces[] = $builder = new InterfaceBuilder($this, $name);

        return $builder;
    }

    public function build(): SourceCode
    {
        return new SourceCode(
            $this->namespace,
            UseStatements::fromQualifiedNames($this->useStatements),
            Classes::fromClasses(array_map(function (ClassBuilder $builder) {
                return $builder->build();
            }, $this->classes)),
            Interfaces::fromInterfaces(array_map(function (InterfaceBuilder $builder) {
                return $builder->build();
            }, $this->interfaces))
        );
    }
}
