// File: source.php
<?php

class TestClass
{
    use Bar;

    use Foo;


    use Baz;
}
// File: expected.php
<?php

class TestClass
{
    use Bar;
    use Foo;
    use Baz;
}
