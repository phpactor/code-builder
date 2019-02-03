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

    /**
     * @var UseFunctionStatements
     */
    private $useFunctionStatements;

    public function __construct(
        NamespaceName $namespace = null,
        UseStatements $useStatements = null,
        Classes $classes = null,
        Interfaces $interfaces = null,
        UpdatePolicy $updatePolicy = null,
        UseFunctionStatements $useFunctionStatements = null
    ) {
        parent::__construct($updatePolicy);
        $this->namespace = $namespace ?: NamespaceName::fromString('');
        $this->useStatements = $useStatements ?: UseStatements::empty();
        $this->classes = $classes ?: Classes::empty();
        $this->interfaces = $interfaces ?: Interfaces::empty();
        $this->updatePolicy = $updatePolicy;
        $this->useFunctionStatements = $useFunctionStatements;
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

    public function useFunctionStatements(): UseFunctionStatements
    {
        return $this->useFunctionStatements;
    }
}
