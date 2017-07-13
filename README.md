Class Builder
=============

[![Build Status](https://travis-ci.org/phpactor/class-transform.svg?branch=master)](https://travis-ci.org/phpactor/class-transform)

Library for generating or applying changes to code:

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
    ->end()
    ->build();

$sourcePrototype = $builder->build();

// apply prototype to existing source code (idempotent)
$sourceBuilder->apply($source, file_get_contents('SomeFile.php'));

// generate source
$code = $sourceBuilder->generate($prototype);

echo (string) $code;
```

Yields:

```
<?php

namespace Animals;

use Measurements\Height;

class Rabbits extends Leopridae
{
    /**
     * @var int
     */
    private $force = 5;

    public function jump(Height $how = 'high')
    {
    }
}
