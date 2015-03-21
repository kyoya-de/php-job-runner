<?php

namespace Kyoya\PhpJobRunner\Workflow;

use Symfony\Component\Yaml\Parser;

class Manager
{
    /**
     * @var Workflow[]
     */
    private $workflows;

    /**
     * @var string
     */
    private $workflowPath;

    /**
     * @param string $workflowPath
     */
    public function __construct($workflowPath)
    {
        $this->workflowPath = $workflowPath;
    }

    /**
     * @param $workflow
     * @param $taskName
     *
     * @throws FileNotFoundException
     * @throws UnknownTaskException
     * @return Task
     */
    public function getTaskServiceId($workflow, $taskName)
    {
        if (!isset($this->workflows[$workflow])) {
            $this->loadWorkflow($workflow);
        }

        return $this->workflows[$workflow]->getTask($taskName);
    }

    /**
     * @param $workflow
     *
     * @throws FileNotFoundException
     * @return Workflow
     */
    public function getWorkflow($workflow)
    {
        if (!isset($this->workflows[$workflow])) {
            $this->loadWorkflow($workflow);
        }

        return $this->workflows[$workflow];
    }

    /**
     * @param $workflowName
     *
     * @throws FileNotFoundException
     */
    protected function loadWorkflow($workflowName)
    {
        $workflowFile = "{$this->workflowPath}/{$workflowName}.yml";
        if (!file_exists($workflowFile)) {
            throw new FileNotFoundException("The workflow configuration file {$workflowName}.yml doesn't exist.");
        }

        $yamlParser = new Parser();

        $workflowConfiguration = $yamlParser->parse(file_get_contents($workflowFile));
        $workflow = new Workflow($workflowName);

        foreach ($workflowConfiguration['tasks'] as $name => $serviceId)
        {
            $workflow->setTask($name, new Task($name, $serviceId));
        }

        $this->workflows[$workflowName] = $workflow;
    }
}
