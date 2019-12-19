// File: source.php
<?php

class Test implements Handler
{
    /**
    * @var string
    */
    private $foo;

    /**
    * Foobar
    */
    public function foobar()
    {
    }
}
// File: expected.php
<?php

class Test implements Handler
{
    /**
     * @var string
     */
    private $foo;

    /**
     * Foobar
     */
    public function foobar()
    {
    }
}
