<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class InterfacePrototype extends ClassLikePrototype
{
    /**
     * @var ExtendsInterfaces
     */
    private $extendsInterfaces;

    public function __construct(
        string $name,
        Methods $methods = null,
        ExtendsInterfaces $extendsInterfaces = null
    ) {
        parent::__construct($name, $methods);
        $this->extendsInterfaces = $extendsInterfaces ?: ExtendsInterfaces::empty();
    }

    public function extendsInterfaces(): ExtendsInterfaces
    {
        return $this->extendsInterfaces;
    }
}
