<?php

namespace Kyoya\PhpJobRunner\Process;

use Symfony\Component\Process\Process as SfProcess;

class Process extends SfProcess
{
    private $stopOnExit = true;

    public function stopOnExit()
    {
        $this->stopOnExit = true;
    }

    public function doNotStopOnExit()
    {
        $this->stopOnExit = false;
    }

    public function __destruct()
    {
        if ($this->stopOnExit) {
            $this->stop();
            return;
        }
    }
}
