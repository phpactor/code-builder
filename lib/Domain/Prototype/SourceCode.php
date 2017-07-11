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
        NamespaceName $namespace = null,
        UseStatements $useStatements = null,
        Properties $properties = null,
        Methods $methods = null
    )
    {
        $this->namespace = $namespace ?: NamespaceName::fromString('');
        $this->useStatements = $useStatements ?: new UseStatements();
        $this->properties = $properties ?: new Properties();
        $this->methods = $methods ?: new Methods;
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

