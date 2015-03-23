<?php

namespace Kyoya\PhpJobRunner\Command;

use Kyoya\PhpJobRunner\Kernel\KernelInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

class Application extends BaseApplication
{

    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        parent::__construct('PHP-Job-Runner', 'v1.0.0');

        $this->getDefinition()->addOption(
            new InputOption(
                '--env',
                '-e',
                InputOption::VALUE_REQUIRED,
                'The Environment name.',
                $kernel->getEnvironment()
            )
        );

        $this->add(new WorkflowExecuteCommand());
        $this->add(new ExecuteTaskCommand());
        $this->add(new ExecuteCommand());

        $this->registerEventDispatcher();
    }

    protected function registerEventDispatcher()
    {
        $dispatcher = new ContainerAwareEventDispatcher($this->kernel->getContainer());
        $dispatcher->addSubscriberService(
            'container_aware_command.event_subscriber',
            '\\Kyoya\\PhpJobRunner\\Command\\ContainerCommandSubscriber'
        );

        $this->setDispatcher($dispatcher);
    }
}
