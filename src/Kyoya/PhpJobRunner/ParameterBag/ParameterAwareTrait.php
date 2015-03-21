<?php

namespace Kyoya\PhpJobRunner\ParameterBag;

trait ParameterAwareTrait
{

    /**
     * @var ParameterBagInterface
     */
    protected $parameters;

    public function setParameters(ParameterBagInterface $parameterBag)
    {
        $this->parameters = $parameterBag;
    }
}
