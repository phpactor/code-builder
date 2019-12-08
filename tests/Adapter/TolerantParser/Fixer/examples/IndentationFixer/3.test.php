// File: source.php
<?php

class TestClass
{
private $bar;
    public function foobar() 
    {
    }
}
// File: expected.php
<?php

class TestClass
{
    private $bar;
    public function foobar() 
    {
    }
}
