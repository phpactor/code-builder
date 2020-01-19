// File: source.php
<?php
return implode("\n", array_map(function (TextEdit $otherEdit) use ($edit) {
    return sprintf(
        '%s%s %s "%s"',
        $edit === $otherEdit ? '> ' : '  ',
        $otherEdit->start,
        $otherEdit->start + $otherEdit->length,
        str_replace("\n", '\n', $otherEdit->content)
    );
}, $edits));
// File: expected.php
<?php
return implode("\n", array_map(function (TextEdit $otherEdit) use ($edit) {
    return sprintf(
        '%s%s %s "%s"',
        $edit === $otherEdit ? '> ' : '  ',
        $otherEdit->start,
        $otherEdit->start + $otherEdit->length,
        str_replace("\n", '\n', $otherEdit->content)
    );
}, $edits));
