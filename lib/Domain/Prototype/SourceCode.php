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

    public function __construct(
        NamespaceName $namespace = null,
        UseStatements $useStatements = null,
        Classes $classes = null
    )
    {
        $this->namespace = $namespace ?: NamespaceName::fromString('');
        $this->useStatements = $useStatements ?: new UseStatements();
        $this->classes = $classes ?: new Classes();
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
}
