// File: source.php
<?php

class TestClass
{
    use Bar;
    const FOOBAR = 'baz';
    private $foo;
    public function bar() {}
}
// File: expected.php
<?php

class TestClass
{
    use Bar;

    const FOOBAR = 'baz';

    private $foo;

    public function bar() {}
}
