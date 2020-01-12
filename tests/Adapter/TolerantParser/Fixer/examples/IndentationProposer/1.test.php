// File: source.php
<?php

class TestClass
{
    private $foo;

    public function foobar()
    {
        $bar = $foo;

        if ($bar) {
            $bar = $foo;
        }
    }
}
// File: expected.php
<?php

class TestClass
{
    private $foo;
    
    public function foobar()
    {
        $bar = $foo;
        
        if ($bar) {
            $bar = $foo;
        }
    }
}
