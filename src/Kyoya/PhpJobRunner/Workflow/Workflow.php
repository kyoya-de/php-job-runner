<?php

namespace Kyoya\PhpJobRunner\Workflow;

class Workflow
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var Task[]
     */
    private $tasks;

    /**
     * @param string $name
     * @param Task[] $tasks
     */
    public function __construct($name, $tasks = array())
    {
        $this->name  = $name;
        $this->tasks = $tasks;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param Task[] $tasks
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * @param string $name
     * @param Task   $task
     */
    public function setTask($name, Task $task)
    {
        $this->tasks[$name] = $task;
    }

    /**
     * @param $name
     *
     * @throws UnknownTaskException
     * @return Task
     */
    public function getTask($name)
    {
        if (!isset($this->tasks[$name])) {
            throw new UnknownTaskException("The task {$name} doesn't exist!");
        }

        return $this->tasks[$name];
    }
}
