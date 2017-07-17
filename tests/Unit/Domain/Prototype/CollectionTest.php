<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Classes;

class ClassesTest extends TestCase
{
    /**
     * @testdox Create from classes
     */
    public function testCreateFromClasses()
    {
        $classes = Classes::fromClasses([
            new ClassPrototype('One'),
            new ClassPrototype('Two'),
        ]);
        $this->assertCount(2, iterator_to_array($classes));
    }

    public function testNotIn()
    {
        $classes = Classes::fromClasses([
            new ClassPrototype('One'),
            new ClassPrototype('Two'),
            new ClassPrototype('Three'),
        ]);
        $this->assertCount(2, $classes->notIn(['One']));
    }

    public function testIn()
    {
        $classes = Classes::fromClasses([
            new ClassPrototype('One'),
            new ClassPrototype('Two'),
            new ClassPrototype('Three'),
        ]);
        $this->assertCount(2, $classes->in(['One', 'Two']));
    }
}
