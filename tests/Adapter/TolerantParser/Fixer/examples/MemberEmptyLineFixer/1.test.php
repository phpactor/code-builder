// File: source.php
<?php

class TestClass
{
    use Bar;

    const FOO = 'bar';

    private $foobar;
    private $barfoo;

    public function barfoo()
    {
    }
}
// File: expected.php
<?php

class TestClass
{
    use Bar;

    const FOO = 'bar';

    private $foobar;
    private $barfoo;

    public function barfoo()
    {
    }
}
