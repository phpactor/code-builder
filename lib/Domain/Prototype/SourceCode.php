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
     * @var Properties
     */
    private $properties;

    /**
     * @var Methods
     */
    private $methods;

    public function __construct(
        NamespaceName $namespace,
        UseStatements $useStatements,
        Properties $properties,
        Methods $methods
    )
    {
        $this->namespace = $namespace;
        $this->useStatements = $useStatements;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    public function namespace(): NamespaceName
    {
        return $this->namespace;
    }

    public function useStatements(): UseStatements
    {
        return $this->useStatements;
    }

    public function properties(): Properties
    {
        return $this->properties;
    }

    public function methods(): Methods
    {
        return $this->methods;
    }
}

