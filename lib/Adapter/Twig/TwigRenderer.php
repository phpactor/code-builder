<?php

namespace Phpactor\CodeBuilder\Adapter\Twig;

use Phpactor\CodeBuilder\Domain\Generator;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Renderer;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Phpactor\CodeBuilder\Adapter\Twig\TwigExtension;

final class TwigRenderer implements Renderer
{
    private $twig;
    private $templateNameResolver;

    public function __construct(
        Environment $twig = null,
        TemplateNameResolver $templateNameResolver = null
    )
    {
        $this->twig = $twig ?: $this->createTwig();
        $this->templateNameResolver = $templateNameResolver ?: new ClassShortNameResolver();
    }

    public function render(Prototype $prototype, string $variant = null): Code
    {
        $templateName = $this->templateNameResolver->resolveName($prototype);

        if ($variant) {
            $templateName = $variant . '/' . $templateName;
        }

        return Code::fromString(rtrim($this->twig->render($templateName, [
            'prototype' => $prototype,
            'generator' => $this,
        ]), PHP_EOL));
    }

    private function createTwig()
    {
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../templates'), [
            'strict_variables' => true,
        ]);
        $twig->addExtension(new TwigExtension($this, '    '));

        return $twig;
    }
}
