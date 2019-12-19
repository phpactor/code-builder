<?php


namespace Phpactor\CodeBuilder\Domain;

/**
 * This class is copied from the Tolerant Parser library.
 */
class TextEdit
{
    public $start;
    public $length;
    public $content;

    public function __construct(int $start, int $length, string $content)
    {
        $this->start = $start;
        $this->length = $length;
        $this->content = $content;
    }

    /**
     * Applies array of edits to the document, and returns the resulting text.
     * Supplied $edits must not overlap, and be ordered by increasing start position.
     *
     * Note that after applying edits, the original AST should be invalidated.
     *
     * @param array | TextEdit[] $edits
     * @param string $text
     * @return string
     */
    public static function applyEdits(array $edits, string $text) : string
    {
        $prevEditStart = PHP_INT_MAX;

        for ($i = \count($edits) - 1; $i >= 0; $i--) {
            $edit = $edits[$i];

            if ($prevEditStart < $edit->start || $prevEditStart < $edit->start + $edit->length) {
                throw new \OutOfBoundsException(sprintf(
                    'Supplied TextEdit[] "%s" must not overlap and be in increasing start position order.',
                    $edit->content
                ));
            }

            if ($edit->start < 0 || $edit->length < 0 || $edit->start + $edit->length > \strlen($text)) {
                throw new \OutOfBoundsException(sprintf(
                    "Applied TextEdit range out of bounds, text length: %s, start: %s, length: %s.",
                    strlen($text),
                    $edit->start,
                    $edit->length
                ));
            }
            $prevEditStart = $edit->start;
            $head = \substr($text, 0, $edit->start);
            $tail = \substr($text, $edit->start + $edit->length);
            $text = $head . $edit->content . $tail;
        }

        return $text;
    }
}
