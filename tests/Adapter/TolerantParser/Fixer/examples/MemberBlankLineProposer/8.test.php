// File: source.php
<?php

class TestClass
{
    public function bar() {}

    public function bar() {}
}
// File: expected.php
<?php

class TestClass
{
    public function bar() {}

    public function bar() {}
}
