<?php

namespace Phpactor\CodeBuilder\Adapter\Twig;

use Phpactor\CodeBuilder\Domain\Generator;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;

final class TwigGenerator implements Generator
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

    public function generate(Prototype $prototype): Code
    {
        $templateName = $this->templateNameResolver->resolveName($prototype);

        return Code::fromString($this->twig->render($templateName, [
            'prototype' => $prototype
        ]));
    }
}
