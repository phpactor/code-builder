<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;

interface BuilderFactory
{
    public function fromSource(string $source): SourceCodeBuilder;
}
