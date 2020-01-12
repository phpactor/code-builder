// File: source.php
<?php

class TestClass
{
public function foobar()
{
    // foo
    echo '12';
}
}
// File: expected.php
<?php

class TestClass
{
    public function foobar()
    {
        // foo
        echo '12';
    }
}
