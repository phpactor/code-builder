<?php

namespace Phpactor\CodeBuilder\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;

abstract class UpdaterTestCase extends TestCase
{
    /**
     * @dataProvider provideNamespaceAndUse
     */
    public function testNamespaceAndUse(string $existingCode, SourceCode $prototype, string $expectedCode)
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideNamespaceAndUse()
    {
        return [
            'It does nothing when given an empty source code protoytpe' => [
                
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->build(),
                <<<'EOT'
class Aardvark
{
}
EOT
            ],
            'It does not change the namespace if it is the same' => [
                
                <<<'EOT'
namespace Animal\Kingdom;

class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->namespace('Animal\Kingdom')->build(),
                <<<'EOT'
namespace Animal\Kingdom;

class Aardvark
{
}
EOT
            ],
            'It adds the namespace if it doesnt exist' => [
                
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->namespace('Animal\Kingdom')->build(),
                <<<'EOT'
namespace Animal\Kingdom;

class Aardvark
{
}
EOT
            ],
            'It updates the namespace' => [
                
                <<<'EOT'
namespace Animal\Kingdom;

class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->namespace('Bovine\Kingdom')->build(),
                <<<'EOT'
namespace Bovine\Kingdom;

class Aardvark
{
}
EOT
            ],
            'It adds use statements' => [
                
                <<<'EOT'
EOT
                , SourceCodeBuilder::create()->use('Bovine')->build(),
                <<<'EOT'

use Bovine;
EOT
            ],
            'It adds use statements after a namespace' => [
                
                <<<'EOT'
namespace Kingdom;
EOT
                , SourceCodeBuilder::create()->use('Bovine')->build(),
                <<<'EOT'
namespace Kingdom;

use Bovine;
EOT
            ],
            'It appends use statements' => [
                
                <<<'EOT'
namespace Kingdom;

use Primate;
EOT
                , SourceCodeBuilder::create()->use('Bovine')->build(),
                <<<'EOT'
namespace Kingdom;

use Primate;
use Bovine;
EOT
            ],
            'It ignores existing use statements' => [
                
                <<<'EOT'
namespace Kingdom;

use Primate;
EOT
                , SourceCodeBuilder::create()->use('Primate')->build(),
                <<<'EOT'
namespace Kingdom;

use Primate;
EOT
            ],
            'It ignores repeated namespaced use statements' => [
                
                <<<'EOT'
namespace Kingdom;

EOT
                , SourceCodeBuilder::create()->use('Primate\Ape')->use('Primate\Ape')->build(),
                <<<'EOT'
namespace Kingdom;

use Primate\Ape;
EOT
            ],
            'It ignores existing aliased use statements' => [
                
                <<<'EOT'
namespace Kingdom;

use Primate as Foobar;
EOT
                , SourceCodeBuilder::create()->use('Primate')->build(),
                <<<'EOT'
namespace Kingdom;

use Primate as Foobar;
EOT
            ],
            'It appends multiple use statements' => [
                
                <<<'EOT'
namespace Kingdom;

use Primate;
EOT
                , SourceCodeBuilder::create()->use('Animal\Bovine')->use('Feline')->use('Canine')->build(),
                <<<'EOT'
namespace Kingdom;

use Primate;
use Animal\Bovine;
use Feline;
use Canine;
EOT
            ],
        ];
    }

    /**
     * @dataProvider provideClasses
     */
    public function testClasses(string $existingCode, SourceCode $prototype, string $expectedCode)
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideClasses()
    {
        return [
            'It does nothing when prototype has only the class' => [
                
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->end()->build(),
                <<<'EOT'
class Aardvark
{
}
EOT
            ],
            'It adds a class to an empty file' => [
                
                <<<'EOT'
EOT
                , SourceCodeBuilder::create()->class('Anteater')->end()->build(),
                <<<'EOT'

class Anteater
{
}
EOT
            ],
            'It adds a class' => [
                
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->class('Anteater')->end()->build(),
                <<<'EOT'
class Aardvark
{
}

class Anteater
{
}
EOT
            ],
            'It adds a class after a namespace' => [
                
                <<<'EOT'
namespace Animals;

class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->class('Anteater')->end()->build(),
                <<<'EOT'
namespace Animals;

class Aardvark
{
}

class Anteater
{
}
EOT
            ],
            'It does not modify a class with a namespace' => [
                
                <<<'EOT'
namespace Animals;

class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->namespace('Animals')->class('Aardvark')->end()->build(),
                <<<'EOT'
namespace Animals;

class Aardvark
{
}
EOT
            ],
            'It adds multiple classes' => [
                <<<'EOT'
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->end()->class('Anteater')->end()->build(),
                <<<'EOT'

class Aardvark
{
}

class Anteater
{
}
EOT
            ],
            'It extends a class' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->extends('Animal')->end()->build(),
                <<<'EOT'
class Aardvark extends Animal
{
}
EOT
            ],
            'It modifies an existing extends' => [
                <<<'EOT'
class Aardvark extends Giraffe
{
}
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->extends('Animal')->end()->build(),
                <<<'EOT'
class Aardvark extends Animal
{
}
EOT
            ],
            'It is idempotent extends' => [
                <<<'EOT'
class Aardvark extends Animal
{
}
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->extends('Animal')->end()->build(),
                <<<'EOT'
class Aardvark extends Animal
{
}
EOT
            ],
            'It is implements an interface' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->implements('Animal')->end()->build(),
                <<<'EOT'
class Aardvark implements Animal
{
}
EOT
            ],
            'It is implements implementss' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->implements('Zoo')->implements('Animal')->end()->build(),
                <<<'EOT'
class Aardvark implements Zoo, Animal
{
}
EOT
            ],
            'It is adds implements' => [
                <<<'EOT'
class Aardvark implements Zoo
{
}
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->implements('Animal')->end()->build(),
                <<<'EOT'
class Aardvark implements Zoo, Animal
{
}
EOT
            ],
            'It ignores existing implements names' => [
                <<<'EOT'
class Aardvark implements Animal
{
}
EOT
                , SourceCodeBuilder::create()->class('Aardvark')->implements('Zoo')->implements('Animal')->end()->build(),
                <<<'EOT'
class Aardvark implements Animal, Zoo
{
}
EOT
            ],
        ];
    }

    /**
     * @dataProvider provideProperties
     */
    public function testProperties(string $existingCode, SourceCode $prototype, string $expectedCode)
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideProperties()
    {
        return [
            'It adds a property' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public $propertyOne;
}
EOT
            ],
            'It adds a property idempotently' => [
                <<<'EOT'
class Aardvark
{
    public $propertyOne;
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public $propertyOne;
}
EOT
            ],
            'It adds a property with existing assigned property' => [
                <<<'EOT'
class Aardvark
{
    public $propertyOne = false;
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public $propertyOne = false;
}
EOT
            ],
            'It adds a property after existing properties' => [
                <<<'EOT'
class Aardvark
{
    public $eyes
    public $nose;
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public $eyes
    public $nose;
    public $propertyOne;
}
EOT
            ],
            'It adds multiple properties' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->end()->property('propertyTwo')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public $propertyOne;
    public $propertyTwo;
}
EOT
            ],
            'It adds a documented properties' => [
                <<<'EOT'
class Aardvark
{
    public $eyes
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->type('Hello')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public $eyes

    /**
     * @var Hello
     */
    public $propertyOne;
}
EOT
            ],
            'It adds before methods' => [
                <<<'EOT'
class Aardvark
{
    public function crawl()
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->property('propertyOne')->type('Hello')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    /**
     * @var Hello
     */
    public $propertyOne;

    public function crawl()
    {
    }
}
EOT
            ]
        ];
    }

    /**
     * @dataProvider provideMethods
     */
    public function testMethods(string $existingCode, SourceCode $prototype, string $expectedCode)
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideMethods()
    {
        return [
            'It adds a method' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function methodOne()
    {
    }
}
EOT
            ],
            'It adds multiple methods' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->end()
                        ->method('methodTwo')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function methodOne()
    {
    }

    public function methodTwo()
    {
    }
}
EOT
            ],
            'It adds parameterized method' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')
                            ->parameter('sniff')
                                ->type('Snort')
                            ->end()
                        ->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function methodOne(Snort $sniff)
    {
    }
}
EOT
            ],
            'It is idempotent' => [
                <<<'EOT'
class Aardvark
{
    public function methodOne()
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function methodOne()
    {
    }
}
EOT
            ],
            'It updates parameters' => [
                <<<'EOT'
class Aardvark
{
    public function methodOne(Snort $sniff)
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')
                            ->parameter('sniff')
                                ->type('Barf')
                            ->end()
                            ->parameter('plod')
                                ->type('Blog')
                            ->end()
                        ->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function methodOne(Barf $sniff, Blog $plod)
    {
    }
}
EOT
            ],
            'It adds parameters' => [
                <<<'EOT'
class Aardvark
{
    public function methodOne()
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')
                            ->parameter('sniff')
                                ->type('Barf')
                            ->end()
                        ->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function methodOne(Barf $sniff)
    {
    }
}
EOT
            ],
            'It adds parameters and leaves existing ones in place' => [
                <<<'EOT'
class Aardvark
{
    public function methodOne($arg1, Hello $hello, $arg2)
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')
                            ->parameter('arg1')
                            ->end()
                            ->parameter('sniff')
                                ->type('Barf')
                            ->end()
                        ->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function methodOne($arg1, Barf $sniff, Hello $hello, $arg2)
    {
    }
}
EOT
            ],
            'It adds a method after existing methods' => [
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
    }

    public function nose()
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
    }

    public function nose()
    {
    }

    public function methodOne()
    {
    }
}
EOT
            ],
            'It adds a documented methods' => [
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->docblock('Hello')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
    }

    /**
     * Hello
     */
    public function methodOne()
    {
    }
}
EOT
            ],
            'It adds a method with a body' => [
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('methodOne')->body()->line('echo "Hello World";')->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
    }

    public function methodOne()
    {
        echo "Hello World";
    }
}
EOT
            ],
            'Add line to a methods body' => [
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('eyes')->body()->line('echo "Hello World";')->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
        echo "Hello World";
    }
}
EOT
            ],
            'Add lines after existing lines' => [
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
        echo "Goodbye world!";
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('eyes')->body()->line('echo "Hello World";')->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
        echo "Goodbye world!";
        echo "Hello World";
    }
}
EOT
            ],
            'Should not add the same line twice' => [
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
        echo "Hello World";
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->method('eyes')->body()->line('echo "Hello World";')->end()->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    public function eyes()
    {
        echo "Hello World";
    }
}
EOT
            ],
        ];
    }

    /**
     * @dataProvider provideConstants
     */
    public function testConstants(string $existingCode, SourceCode $prototype, string $expectedCode)
    {
        $this->assertUpdate($existingCode, $prototype, $expectedCode);
    }

    public function provideConstants()
    {
        return [
            'It adds a constant' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->constant('constantOne', 'foo')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    const constantOne = 'foo';
}
EOT
            ],
            'It adds is idempotent' => [
                <<<'EOT'
class Aardvark
{
    const constantOne = 'aaa';
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->constant('constantOne', 'aaa')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    const constantOne = 'aaa';
}
EOT
            ],
            'It adds a constant after existing constants' => [
                <<<'EOT'
class Aardvark
{
    const constantOne = 'aaa';
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->constant('constantTwo', 'bbb')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    const constantOne = 'aaa';
    const constantTwo = 'bbb';
}
EOT
            ],
            'It adds multiple constants' => [
                <<<'EOT'
class Aardvark
{
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                        ->constant('constantOne', 'a')->end()->constant('constantTwo', 'b')->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    const constantOne = 'a';
    const constantTwo = 'b';
}
EOT
            ],
            'It adds before methods' => [
                <<<'EOT'
class Aardvark
{
    public function crawl()
    {
    }
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                    ->constant('constantOne', 1)->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    const constantOne = 1;

    public function crawl()
    {
    }
}
EOT
            ],
            'It adds before properties' => [
                <<<'EOT'
class Aardvark
{
    private $crawlSpace;
}
EOT
                , SourceCodeBuilder::create()
                    ->class('Aardvark')
                    ->constant('constantOne', 1)->end()
                    ->end()
                    ->build(),
                <<<'EOT'
class Aardvark
{
    const constantOne = 1;

    private $crawlSpace;
}
EOT
            ]
        ];
    }


    abstract protected function updater(): Updater;

    private function assertUpdate(string $existingCode, SourceCode $prototype, string $expectedCode)
    {
        $code = $this->updater()->apply($prototype, Code::fromString('<?php'.PHP_EOL.$existingCode));
        $this->assertEquals('<?php'.PHP_EOL. $expectedCode, (string) $code);
    }
}
