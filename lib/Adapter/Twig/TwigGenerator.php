<?php

namespace Phpactor\CodeBuilder\Adapter\Twig;

use Phpactor\CodeBuilder\Domain\Generator;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Renderer;

final class TwigGenerator implements Renderer
{
    private $twig;
    private $templateNameResolver;

    public function __construct(
        \Twig_Environment $twig,
        TemplateNameResolver $templateNameResolver = null
    )
    {
        $this->twig = $twig;
        $this->templateNameResolver = $templateNameResolver ?: new ClassShortNameResolver();
    }

    public function render(Prototype $prototype): Code
    {
        $templateName = $this->templateNameResolver->resolveName($prototype);

        return Code::fromString(rtrim($this->twig->render($templateName, [
            'prototype' => $prototype,
            'generator' => $this,
        ]), PHP_EOL));
    }
}
