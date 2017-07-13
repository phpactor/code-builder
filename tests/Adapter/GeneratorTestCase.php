<?php

namespace Phpactor\CodeBuilder\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Generator;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Classes;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Prototype\Methods;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Phpactor\CodeBuilder\Domain\Prototype\Parameter;
use Phpactor\CodeBuilder\Domain\Prototype\Parameters;
use Phpactor\CodeBuilder\Domain\Prototype\Properties;
use Phpactor\CodeBuilder\Domain\Prototype\Property;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Prototype\QualifiedName;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatements;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\SourceText;
use Phpactor\CodeBuilder\Domain\Prototype\ReturnType;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;

abstract class GeneratorTestCase extends TestCase
{
    abstract protected function generator(): Generator;
    /**
     * @testdox It should use twig to generate a template
     * @dataProvider provideGenerate
     */
    public function testGenerate(Prototype $prototype, string $expectedCode)
    {
        $code = $this->generator()->generate($prototype);
        $this->assertEquals(rtrim(Code::fromString($expectedCode), PHP_EOL), rtrim($code, PHP_EOL));
    }

    public function provideGenerate()
    {
        return [
            'Generates an empty PHP file' => [
                new SourceCode(),
                '<?php',
            ],
            'Generates a PHP file with a namespace' => [
                new SourceCode(
                    NamespaceName::fromString('Acme')
                ),
                <<<'EOT'
<?php

namespace Acme;
EOT
            ],
            'Generates a class' => [
                new ClassPrototype('Dog'),
                <<<'EOT'
class Dog
{
}
EOT
            ],
            'Generates source code with classes' => [
                new SourceCode(
                    NamespaceName::root(),
                    UseStatements::empty(),
                    Classes::fromClasses([ new ClassPrototype('Dog'), new ClassPrototype('Cat') ])
                ),
                <<<'EOT'
<?php

class Dog
{
}

class Cat
{
}
EOT
            ],
            'Generates source code with use statements' => [
                new SourceCode(
                    NamespaceName::root(),
                    UseStatements::fromQualifiedNames([
                        QualifiedName::fromString('Acme\Post\Board'),
                        QualifiedName::fromString('Acme\Post\Zebra')
                    ])
                ),
                <<<'EOT'
<?php

use Acme\Post\Board;
use Acme\Post\Zebra;
EOT
            ],
            'Generates a class with properties' => [
                new ClassPrototype(
                    'Dog',
                    Properties::fromProperties([
                        new Property('planes')
                    ])
                ),
                <<<'EOT'
class Dog
{
    public $planes;
}
EOT
            ],
            'Generates a property' => [
                new Property('planes'),
                <<<'EOT'
public $planes;
EOT
            ],
            'Generates private properties with default value' => [
                new Property('trains', Visibility::private(), DefaultValue::null()),
                <<<'EOT'
private $trains = null;
EOT
            ],
            'Generates a class with methods' => [
                new ClassPrototype(
                    'Dog',
                    Properties::empty(),
                    Methods::fromMethods([
                        new Method('hello'),
                    ])
                ),
                <<<'EOT'
class Dog
{
    public function hello()
    {
    }
}
EOT
            ],
            'Generates a method parameters' => [
                new Method('hello', Visibility::private(), Parameters::fromParameters([
                    new Parameter('one'),
                    new Parameter('two', Type::fromString('string')),
                    new Parameter('three', Type::none(), DefaultValue::fromValue(42)),
                ])),
                <<<'EOT'
private function hello($one, string $two, $three = 42)
EOT
            ],
            'Generates method return type' => [
                new Method(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::fromString('Hello')
                ),
                <<<'EOT'
private function hello(): Hello
EOT
            ],
            'Generates a class with a parent' => [
                new ClassPrototype(
                    'Kitten',
                    Properties::empty(),
                    Methods::empty(),
                    ExtendsClass::fromString('Cat')
                ),
                <<<'EOT'
class Kitten extends Cat
{
}
EOT
            ],
            'Generates a class with interfaces' => [
                new ClassPrototype(
                    'Kitten',
                    Properties::empty(),
                    Methods::empty(),
                    ExtendsClass::none(),
                    ImplementsInterfaces::fromTypes([
                        Type::fromString('Feline'),
                        Type::fromString('Infant')
                    ])
                ),
                <<<'EOT'
class Kitten implements Feline, Infant
{
}
EOT
            ],
            'Generates a property with a comment' => [
                new Property(
                    'planes',
                    Visibility::public(),
                    DefaultValue::none(),
                    Type::fromString('PlaneCollection')
                ),
                <<<'EOT'
/**
 * @var PlaneCollection
 */
public $planes;
EOT
            ],
        ];
    }

    public function testBuilder()
    {
        $expected = <<<'EOT'
<?php

namespace Animals;

use Measurements\Height;

class Rabbits extends Leopridae
{
    /**
     * @var int
     */
    private $force = 5;

    public $guile;

    public function jump(Height $how = 'high')
    {
    }

    public function bark(int $volume)
    {
    }
}
EOT
        ;
        $source = $builder = SourceCodeBuilder::create()
            ->namespace('Animals')
            ->use('Measurements\\Height')
            ->class('Rabbits')
                ->extends('Leopridae')
                ->property('force')
                    ->visibility('private')
                    ->type('int')
                    ->defaultValue(5)
                ->end()
                ->property('guile')->end()
                ->method('jump')
                    ->parameter('how')
                        ->defaultValue('high')
                        ->type('Height')
                    ->end()
                ->end()
                ->method('bark')
                    ->parameter('volume')
                        ->type('int')
                    ->end()
                ->end()
            ->end()
            ->build();

        $code = $this->generator()->generate($source);

        $this->assertEquals($expected, (string) $code);
    }
}
