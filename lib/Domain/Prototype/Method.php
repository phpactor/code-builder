<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\Lines;
use Phpactor\CodeBuilder\Domain\Prototype\MethodBody;

final class Method extends Prototype
{
    /**
     * @var MethodHeader
     */
    private $header;

    /**
     * @var MethodBody
     */
    private $body;

    public function __construct(
        MethodHeader $header,
        MethodBody $body = null
    )
    {
        $this->header = $header;
        $this->body = $body ?: MethodBody::empty();
    }

    public function header(): MethodHeader
    {
        return $this->header;
    }

    public function body(): MethodBody
    {
        return $this->body;
    }
}
