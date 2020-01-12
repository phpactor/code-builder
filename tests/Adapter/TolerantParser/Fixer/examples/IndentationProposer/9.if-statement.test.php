// File: source.php
<?php

class extractMethod5
{
    public function smallMethod()
    {
        if ($foo) {
        }
    }
}
// File: expected.php
<?php

class extractMethod5
{
    public function smallMethod()
    {
        if ($foo) {
        }
    }
}
