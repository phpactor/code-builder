// File: source.php
<?php

class TestClass
{
    /**
     * @var Foo
     */
    private $foo;

    /**
     * @var Foo
     */
    private $bar;

    public function bar() {}
}
// File: expected.php
<?php

class TestClass
{
    /**
     * @var Foo
     */
    private $foo;

    /**
     * @var Foo
     */
    private $bar;

    public function bar() {}
}
