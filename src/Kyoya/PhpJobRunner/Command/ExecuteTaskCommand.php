<?php

namespace Kyoya\PhpJobRunner\Command;

use Kyoya\PhpJobRunner\ParameterBag\ParameterAwareInterface;
use Kyoya\PhpJobRunner\ParameterBag\ParameterBag;
use Kyoya\PhpJobRunner\StorageProvider\IOException;
use Kyoya\PhpJobRunner\StorageProvider\ProviderInterface;
use Kyoya\PhpJobRunner\Workflow\ExecutableTaskInterface;
use Kyoya\PhpJobRunner\Workflow\InvalidTaskClassException;
use Kyoya\PhpJobRunner\Workflow\Manager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ExecuteTaskCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('workflow:task:execute')
            ->setDescription('Executes a task from a workflow. This is primarily internally used.')
            ->addArgument('workflow', InputArgument::REQUIRED)
            ->addArgument('task', InputArgument::REQUIRED)
            ->addArgument('id', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workflowName = $input->getArgument('workflow');
        $taskName = $input->getArgument('task');
        $taskId = $input->getArgument('id');

        /** @var Manager $workflowManager */
        $workflowManager = $this->container->get('workflow.manager');

        $workflow = $workflowManager->getWorkflow($workflowName);
        $task = $workflow->getTask($taskName);

        $this->runTask($workflow->getName(), $task->getService(), $taskId);
    }

    /**
     * @param string $workflow
     * @param string $taskServiceId
     * @param string $taskId
     *
     * @throws InvalidTaskClassException
     */
    private function runTask($workflow, $taskServiceId, $taskId)
    {
        $task = $this->container->get("{$workflow}.task.{$taskServiceId}");

        if (!$task instanceof ExecutableTaskInterface) {
            throw new InvalidTaskClassException(
                "The service {$taskServiceId} must implement the ExecutableTaskInterface interface."
            );
        }

        if ($task instanceof ContainerAwareInterface) {
            $task->setContainer($this->container);
        }

        if ($task instanceof LoggerAwareInterface) {
            /** @var LoggerInterface $logger */
            $logger = $this->container->get('logger');
            $task->setLogger($logger);
        }

        $parameters = new ParameterBag();
        $parameterBagId = "{$workflow}{$taskId}";

        /** @var ProviderInterface $stateStorage */
        $stateStorage = $this->container->get('state_storage');

        // Try to load the state file.
        try {
            $stateStorage->load($parameterBagId, $parameters);
        } catch (IOException $ignored) {
        }

        if ($task instanceof ParameterAwareInterface) {
            $task->setParameters($parameters);
        }

        $task->execute();

        $stateStorage->persist($parameterBagId, $parameters);
    }
}
