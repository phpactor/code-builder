Code Builder
============

[![Build Status](https://travis-ci.org/phpactor/code-builder.svg?branch=master)](https://travis-ci.org/phpactor/code-builder)

This library can be used to *generate* or idempotently *add* **classes**, **methods**, **properties**, **use
statements**, etc. to existing source code using *prototypes*.

A prototype is an object which defines structural code elements.

Usage
-----

The library provides a source code prototype builder:

```php
$builder = SourceBuilder::create()
    ->namespace('Animals');
    ->use('Measurements\\Height');
    ->class('Rabbits')
        ->extends('Leopridae')
        ->property('force')
            ->visibility('private')
            ->type('int')
            ->defaultValue(5)
        ->end()
        ->method('jump')
            ->parameters()
                ->parameter('how')
                    ->default('high')
                    ->type('Height')
                ->end();
            ->end()
        ->end()
    ->end();

$sourcePrototype = $builder->build();
```

the above prototype can either be used to generate a new class:

```php
$renderer = new TwigRenderer();
$renderer->render($sourcePrototype);
```

Or it can be applied to an existing source code, given the following:


```php
<?php

class Rabbits
{
}
```

When we do:

```php
$updater = new TolerantUpdater();
$updater->apply($sourcePrototype, Code::fromString(file_get_contents('Rabbits.php')));
```

Then we get:

```php
<?php

namespace Animals;

use Measurements\Height;

class Rabbits extends Leopridae
{
    private $force = 5;

    public function jump(Height $how = 'high')
    {
    }
}
```

About this project
------------------

This library is part of the [phpactor](https://github.com/phpactor/phpactor)
project.
