<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Prototype
{
    protected $updatePolicy;

    public function __construct(UpdatePolicy $updatePolicy = null)
    {
        $this->updatePolicy = $updatePolicy ?: UpdatePolicy::update();
    }

    public function applyUpdate(): UpdatePolicy
    {
        return $this->updatePolicy;
    }
}
