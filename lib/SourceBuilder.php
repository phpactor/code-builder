<?php

namespace Phpactor\CodeBuilder;

use Phpactor\CodeBuilder\Domain\CodeFilter;
use Phpactor\CodeBuilder\Domain\CodeFilter\NullCodeFilter;
use Phpactor\CodeBuilder\Domain\Prototype;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Code;

class SourceBuilder
{
    /**
     * @var Renderer
     */
    private $generator;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var CodeFilter
     */
    private $filter;

    public function __construct(
        Renderer $generator,
        Updater $updater,
        CodeFilter $filter = null
    ) {
        $this->generator = $generator;
        $this->updater = $updater;
        $this->filter = $filter ?: new NullCodeFilter();
    }

    public function render(Prototype\Prototype $prototype): Code
    {
        return $this->filter->filter($this->generator->render($prototype));
    }

    public function apply(Prototype\Prototype $prototype, Code $code): Code
    {
        return $this->filter->filter($this->updater->apply($prototype, $code));
    }
}
