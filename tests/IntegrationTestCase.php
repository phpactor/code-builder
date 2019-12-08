<?php

namespace Phpactor\CodeBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

abstract class IntegrationTestCase extends TestCase
{
    public function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
