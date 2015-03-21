<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 21.03.2015
 * Time: 20:06
 */

namespace Kyoya\PhpJobRunner\ParameterBag;


interface ParameterAwareInterface
{
    public function setParameters(ParameterBagInterface $parameterBag);
}
