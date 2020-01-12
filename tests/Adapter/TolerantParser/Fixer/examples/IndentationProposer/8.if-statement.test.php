// File: source.php
<?php

class TestClass
{
    public function foo()
    {
        if (true) {
        $bar = 1;
        $boo = 2;
            }
    }
}
// File: expected.php
<?php

class TestClass
{
    public function foo()
    {
        if (true) {
            $bar = 1;
            $boo = 2;
        }
    }
}
