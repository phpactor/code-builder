<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class SourceCode extends Prototype
{
    /**
     * @var NamespaceName
     */
    private $namespace;

    /**
     * @var UseStatements
     */
    private $useStatements;

    /**
     * @var Classes
     */
    private $classes;

    /**
     * @var Interfaces
     */
    private $interfaces;

    public function __construct(
        NamespaceName $namespace = null,
        UseStatements $useStatements = null,
        Classes $classes = null,
        Interfaces $interfaces = null
    ) {
        $this->namespace = $namespace ?: NamespaceName::fromString('');
        $this->useStatements = $useStatements ?: UseStatements::empty();
        $this->classes = $classes ?: Classes::empty();
        $this->interfaces = $interfaces ?: Interfaces::empty();
    }

    public function namespace(): NamespaceName
    {
        return $this->namespace;
    }

    public function useStatements(): UseStatements
    {
        return $this->useStatements;
    }

    public function classes(): Classes
    {
        return $this->classes;
    }

    public function interfaces(): Interfaces
    {
        return $this->interfaces;
    }
}
