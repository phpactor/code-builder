<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\TolerantParser;

use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\DocblockIndentationProposer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\IndentationProposer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\StyleProposer\MemberBlankLineProposer;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantStyleFixer;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantUpdater;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Tests\Adapter\UpdaterTestCase;

class TolerantUpdaterTest extends UpdaterTestCase
{
    protected function updater(): Updater
    {
        return new TolerantUpdater(new TwigRenderer(), null, null, new TolerantStyleFixer(
            null,
            new MemberBlankLineProposer(new TextFormat()),
            new IndentationProposer(new TextFormat()),
            new DocblockIndentationProposer(new TextFormat())
        ));
    }
}
