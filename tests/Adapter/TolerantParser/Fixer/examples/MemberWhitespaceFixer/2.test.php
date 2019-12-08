// File: source.php
<?php

class TestClass
{
    use Bar;

    use Foo;
}
// File: expected.php
<?php

class TestClass
{
    use Bar;
    use Foo;
}
