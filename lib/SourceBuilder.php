<?php

namespace Phpactor\CodeBuilder;

use Phpactor\CodeBuilder\Domain\Prototype;
use Phpactor\CodeBuilder\Domain\Generator;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\SourceCode;

class SourceBuilder
{
    /**
     * @var SourceGenerator
     */
    private $generator;

    /**
     * @var SourceMutator
     */
    private $updater;

    public function __construct(
        Generator $generator,
        Updater $updater
    )
    {
        $this->generator = $generator;
        $this->updater = $updater;
    }

    public function generate(Prototype\Prototype $prototype)
    {
        return $this->generator->generate($prototype);
    }

    public function apply(Prototype\Prototype $prototype, SourceCode $code)
    {
        return $this->updater->apply($prototype, $code);
    }
}

