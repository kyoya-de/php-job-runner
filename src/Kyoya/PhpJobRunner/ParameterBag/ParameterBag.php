<?php

namespace Kyoya\PhpJobRunner\ParameterBag;

use \Traversable;

class ParameterBag implements ParameterBagInterface
{

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Clears all parameters.
     *
     * @api
     */
    public function clear()
    {
        $this->parameters = array();
    }

    /**
     * Adds parameters to the bag.
     *
     * @param array $parameters An array of parameters
     *
     * @api
     */
    public function add(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Gets the parameters.
     *
     * @return array An array of parameters
     *
     * @api
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed The parameter value
     *
     * @throws ParameterNotFoundException if the parameter is not defined
     *
     * @api
     */
    public function get($name)
    {
        if (!isset($this->parameters[$name])) {
            throw new ParameterNotFoundException("The requested parameter '{$name}' doesn't exist.");
        }

        return $this->parameters[$name];
    }

    /**
     * Sets a parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     *
     * @api
     */
    public function set($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Returns true if a parameter name is defined.
     *
     * @param string $name The parameter name
     *
     * @return bool true if the parameter name is defined, false otherwise
     *
     * @api
     */
    public function has($name)
    {
        return isset($this->parameters[$name]);
    }
}
