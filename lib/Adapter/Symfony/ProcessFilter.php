<?php

namespace Phpactor\CodeBuilder\Adapter\Symfony;

use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\CodeFilter;
use RuntimeException;
use Symfony\Component\Process\Process;

class ProcessFilter implements CodeFilter
{
    /**
     * @var string
     */
    private $binPath;

    /**
     * @var string
     */
    private $commandTemplate;

    public function __construct(string $commandTemplate)
    {
        $this->commandTemplate = $commandTemplate;
    }

    public function filter(Code $code): Code
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'phpactor_code_builder');

        if (false === file_put_contents($tmpName, $code->__toString())) {
            throw new RuntimeException(sprintf(
                'Could not write to temporary file "%s"',
                $tmpName
            ));
        }

        $command = strtr($this->commandTemplate, [
            '%file%' => $tmpName
        ]);

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (false === $process->isSuccessful()) {
            throw new RuntimeException(sprintF(
                'External code filter process failed: "%s"',
                $process->getErrorOutput()
            ));
        }

        return Code::fromString(file_get_contents($tmpName));
    }
}
