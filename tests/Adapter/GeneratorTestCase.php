<?php

namespace Phpactor\CodeBuilder\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Classes;
use Phpactor\CodeBuilder\Domain\Prototype\Docblock;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Prototype\MethodHeader;
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
use Phpactor\CodeBuilder\Domain\Prototype\Interfaces;
use Phpactor\CodeBuilder\Domain\Prototype\InterfacePrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Lines;
use Phpactor\CodeBuilder\Domain\Prototype\Line;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\MethodBody;
use Phpactor\CodeBuilder\Domain\Prototype\Method;


abstract class GeneratorTestCase extends TestCase
{
    abstract protected function renderer(): Renderer;
    /**
     * @testdox It should use twig to render a template
     * @dataProvider provideRender
     */
    public function testRender(Prototype $prototype, string $expectedCode)
    {
        $code = $this->renderer()->render($prototype);
        $this->assertEquals(rtrim(Code::fromString($expectedCode), PHP_EOL), rtrim($code, PHP_EOL));
    }

    public function provideRender()
    {
        return [
            'Renders an empty PHP file' => [
                new SourceCode(),
                '<?php',
            ],
            'Renders a PHP file with a namespace' => [
                new SourceCode(
                    NamespaceName::fromString('Acme')
                ),
                <<<'EOT'
<?php

namespace Acme;
EOT
            ],
            'Renders source code with classes' => [
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
            'Renders source code with interfacess' => [
                new SourceCode(
                    NamespaceName::root(),
                    UseStatements::empty(),
                    Classes::empty(),
                    Interfaces::fromInterfaces([ new InterfacePrototype('Cat'), new InterfacePrototype('Squirrel') ])
                ),
                <<<'EOT'
<?php

interface Cat
{
}

interface Squirrel
{
}
EOT
            ],
            'Renders source code with use statements' => [
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
            'Renders a class' => [
                new ClassPrototype('Dog'),
                <<<'EOT'
class Dog
{
}
EOT
            ],
            'Renders a class with properties' => [
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
            'Renders a property' => [
                new Property('planes'),
                <<<'EOT'
public $planes;
EOT
            ],
            'Renders private properties with default value' => [
                new Property('trains', Visibility::private(), DefaultValue::null()),
                <<<'EOT'
private $trains = null;
EOT
            ],
            'Renders a class with methods' => [
                new ClassPrototype(
                    'Dog',
                    Properties::empty(),
                    Methods::fromMethods([
                        new Method(
                            new MethodHeader('hello')
                        )
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
            'Renders a method parameters' => [
                new MethodHeader('hello', Visibility::private(), Parameters::fromParameters([
                    new Parameter('one'),
                    new Parameter('two', Type::fromString('string')),
                    new Parameter('three', Type::none(), DefaultValue::fromValue(42)),
                ])),
                <<<'EOT'
private function hello($one, string $two, $three = 42)
EOT
            ],
            'Renders static method' => [
                new MethodHeader(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::none(),
                    MethodHeader::IS_STATIC
                ),
                <<<'EOT'
private static function hello()
EOT
            ],
            'Renders method with body' => [
                new MethodHeader(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::none(),
                    0,
                    MethodBody::fromLines(Lines::fromLines([
                        Line::fromString('$this->foobar = FOO')
                    ]))
                ),
                <<<'EOT'
private static function hello()
{
    $this->foobar = FOO;
}
EOT
            ],
            'Renders abstract method' => [
                new MethodHeader(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::none(),
                    MethodHeader::IS_ABSTRACT
                ),
                <<<'EOT'
abstract private function hello()
EOT
            ],
            'Renders method with a docblock' => [
                new MethodHeader(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::fromString('Hello bob')
                ),
                <<<'EOT'
/**
 * Hello bob
 */
private function hello()
EOT
            ],
            'Renders method with a with special chars' => [
                new MethodHeader(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::none(),
                    Docblock::fromString('<hello bob>')
                ),
                <<<'EOT'
/**
 * <hello bob>
 */
private function hello()
EOT
            ],
            'Renders method return type' => [
                new MethodHeader(
                    'hello',
                    Visibility::private(),
                    Parameters::empty(),
                    ReturnType::fromString('Hello')
                ),
                <<<'EOT'
private function hello(): Hello
EOT
            ],
            'Renders a class with a parent' => [
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
            'Renders a class with interfaces' => [
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
            'Renders a property with a comment' => [
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
            'Renders an interface' => [
                new InterfacePrototype('Dog'),
                <<<'EOT'
interface Dog
{
}
EOT
            ],
            'Renders an interface with methods' => [
                new InterfacePrototype('Dog', Methods::fromMethods([
                    new MethodHeader('hello'),
                ])),
                <<<'EOT'
interface Dog
{
    public function hello();
}
EOT
            ],
        ];
    }

    public function testFromBuilder()
    {
        $expected = <<<'EOT'
<?php

namespace Animals;

use Measurements\Height;

interface Animal
{
    public function sleep();
}

class Rabbits extends Leopridae implements Animal
{
    /**
     * @var int
     */
    private $force = 5;

    public $guile;

    /**
     * All the world will be your enemy, prince with a thousand enemies
     */
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
                ->implements('Animal')
                ->property('force')
                    ->visibility('private')
                    ->type('int')
                    ->defaultValue(5)
                ->end()
                ->property('guile')->end()
                ->method('jump')
                    ->docblock('All the world will be your enemy, prince with a thousand enemies')
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
            ->interface('Animal')
                ->method('sleep')->end()
            ->end()
            ->build();

        $code = $this->renderer()->render($source);

        $this->assertEquals($expected, (string) $code);
    }
}
