<?php

namespace Kyoya\PhpJobRunner\Command;

use Kyoya\PhpJobRunner\Workflow\Manager;
use Monolog\Logger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkflowExecuteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('workflow:execute')
            ->setDescription('Execute a workflow')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the workflow to execute.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workflowName = $input->getArgument('name');

        /** @var Manager $workflowManager */
        $workflowManager = $this->container->get('workflow.manager');

        /** @var Logger $logger */
        $logger = $this->container->get('logger');

        $logger->addNotice("Loading workflow configuration {$workflowName}.");
        $workflow = $workflowManager->getWorkflow($workflowName);

        /** @var ContainerAwareCommand $ommand */
        $command = $this->getApplication()->find('workflow:task:execute');

        $pid = getmypid();

        foreach ($workflow->getTasks() as $task) {
            $logger->addNotice(
                "Executing task.",
                array(
                    $workflow->getName(),
                    $task->getName(),
                    $task->getService()
                )
            );

            $cmdInput = new ArrayInput(
                array(
                    'command' => 'workflow:task:execute',
                    'workflow' => $workflow->getName(),
                    'task' => $task->getName(),
                    'id' => $pid,
                )
            );

            if ($command instanceof ContainerAwareCommand) {
                $command->setContainer($this->container);
            }

            $command->run($cmdInput, $output);
        }
    }
}
