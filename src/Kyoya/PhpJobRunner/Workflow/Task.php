<?php

namespace Kyoya\PhpJobRunner\Workflow;

class Task
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $service;

    /**
     * @param string $name
     * @param string $service
     */
    public function __construct($name, $service)
    {
        $this->name = $name;
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }
}
