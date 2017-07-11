Class Builder
=============

[![Build Status](https://travis-ci.org/phpactor/class-transform.svg?branch=master)](https://travis-ci.org/phpactor/class-transform)

Library for generating or applying changes to code:

```php
$builder = $sourceBuilder->prototypeBuilder();
$builder->namespace('Animals');
$builder->use('Measurements\\Height');
$builder->class('Rabbits')
    ->methods()
        ->method('jump')
            ->position(0) // prepend
            ->parameters()
                ->parameter('how)
                    ->default('high')
                    ->type('Measurements\\Height')
                ->end();

$prototype = $builder->build();

// apply prototype to existing source code (idempotent)
$sourceBuilder->apply($prototype, file_get_contents('SomeFile.php'));

// generate source
$sourceBuilder->generate($prototype);
```


