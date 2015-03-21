<?php

namespace Kyoya\PhpJobRunner\Command;

use Kyoya\PhpJobRunner\Process\Process;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\ProcessUtils;

class ExecuteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('execute')
            ->setDescription('Executes all workflows.')
            ->addOption(
                'isolate',
                'i',
                InputOption::VALUE_NONE,
                'Executes each workflow in a separate process in background.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $finder->files()->in($this->container->getParameter('workflows_dir'))->name('*.yml');
        $isolate = $input->getOption('isolate');


        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $workflow = $file->getBasename('.yml');
            if ($isolate) {
                $this->runIsolated($input, $workflow);

                continue;
            }

            $cmdInput = new ArrayInput(
                array(
                    'command' => 'workflow:execute',
                    'name' => $workflow
                )
            );

            $command = $this->getApplication()->find('workflow:execute');
            if ($command instanceof ContainerAwareCommand) {
                $command->setContainer($this->container);
            }
            $command->run($cmdInput, $output);
        }
    }

    /**
     * @param InputInterface $input
     * @param                $workflow
     */
    protected function runIsolated(InputInterface $input, $workflow)
    {
        $processArgs = array('php', 'app/pjr');

        foreach ($input->getOptions() as $key => $value) {
            if (null === $value || false === $value || in_array($key, array('i', 'isolate'))) {
                continue;
            }

            $arg = "-";
            if (1 < strlen($key)) {
                $arg .= "-";
            }
            $arg .= $key;
            if (true !== $value) {
                $arg .= "={$value}";
            }

            $processArgs[] = $arg;
        }

        $processArgs[] = 'workflow:execute';
        $processArgs[] = $workflow;

        $cmdLine = '';
        foreach ($processArgs as $arg) {
            $cmdLine .= ProcessUtils::escapeArgument($arg) . " ";
        }

        $process = new Process($cmdLine);
        $process->doNotStopOnExit();
        $process->disableOutput();
        $process->start();
    }
}
