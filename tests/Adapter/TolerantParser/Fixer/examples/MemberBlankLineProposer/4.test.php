// File: source.php
<?php

class TestClass
{
    use Bar;
    const FOOBAR = 'baz';
}
// File: expected.php
<?php

class TestClass
{
    use Bar;

    const FOOBAR = 'baz';
}
