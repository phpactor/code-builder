<?php

namespace Phpactor\CodeBuilder\Domain;

final class StyleFixer
{
    /**
     * @var StyleProposer[]
     */
    private $propsers;

    public function __construct(StyleProposer ...$propsers)
    {
        $this->propsers = $propsers;
    }

    public function fix(string $code): string
    {
        foreach ($this->propsers as $proposer) {
            $code = $proposer->propose($code)->apply($code);
        }

        return $code;
    }

    public function fixIntersection(string $code, TextEdits $previouslyAppliedChanges): string
    {
        foreach ($this->propsers as $proposer) {
            $code = $proposer->propose($code)->intersection($previouslyAppliedChanges)->apply($code);
        }

        return $code;
    }
}
