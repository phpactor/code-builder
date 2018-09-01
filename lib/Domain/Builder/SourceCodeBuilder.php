<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\Classes;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatements;
use Phpactor\CodeBuilder\Domain\Prototype\Interfaces;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;

class SourceCodeBuilder extends AbstractBuilder
{
    /**
     * @var NamespaceName
     */
    private $namespace;

    /**
     * @var UseStatement[]
     */
    private $useStatements = [];

    /**
     * @var ClassBuilder[]
     */
    protected $classes = [];

    /**
     * @var InterfaceBuilder[]
     */
    protected $interfaces = [];

    public static function create(): SourceCodeBuilder
    {
        return new self();
    }

    public static function childNames(): array
    {
        return [
            'classes',
            'interfaces',
        ];
    }

    public function namespace(string $namespace): SourceCodeBuilder
    {
        $this->namespace = NamespaceName::fromString($namespace);

        return $this;
    }

    public function use(string $use, string $alias = null): SourceCodeBuilder
    {
        $this->useStatements[$use] = UseStatement::fromTypeAndAlias($use, $alias);

        return $this;
    }

    public function class(string $name): ClassBuilder
    {
        if (isset($this->classes[$name])) {
            return $this->classes[$name];
        }

        $this->classes[$name] = $builder = new ClassBuilder($this, $name);

        return $builder;
    }

    public function classLike(string $name): ClassLikeBuilder
    {
        if (isset($this->classes[$name])) {
            return $this->classes[$name];
        }

        if (isset($this->interfaces[$name])) {
            return $this->interfaces[$name];
        }

        throw new InvalidArgumentException(
            'classLike can only be called as an accessor. Use class() or interface() instead'
        );
    }


    public function interface(string $name): InterfaceBuilder
    {
        if (isset($this->interfaces[$name])) {
            return $this->interfaces[$name];
        }

        $this->interfaces[$name] = $builder = new InterfaceBuilder($this, $name);

        return $builder;
    }

    public function build(): SourceCode
    {
        return new SourceCode(
            $this->namespace,
            UseStatements::fromUseStatements($this->useStatements),
            Classes::fromClasses(array_map(function (ClassBuilder $builder) {
                return $builder->build();
            }, $this->classes)),
            Interfaces::fromInterfaces(array_map(function (InterfaceBuilder $builder) {
                return $builder->build();
            }, $this->interfaces))
        );
    }
}
