<?php

namespace Kyoya\PhpJobRunner\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

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
        $command = $this->getApplication()->find('workflow:execute');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $workflow = $file->getBasename('.yml');
            if ($isolate) {
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

                $processBuilder = new ProcessBuilder($processArgs);
                $processBuilder->disableOutput();
                $processBuilder->getProcess()->start();

                continue;
            }

            $cmdInput = new ArrayInput(
                array(
                    'workflow' => $workflow
                )
            );

            $command->run($cmdInput, $output);
        }
    }
}
