<?php

namespace Kyoya\PhpJobRunner\Workflow;

use Kyoya\PhpJobRunner\ParameterBag\ParameterAwareInterface;
use Kyoya\PhpJobRunner\ParameterBag\ParameterAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class ExecutableTaskAbstract
    implements ExecutableTaskInterface, ContainerAwareInterface, LoggerAwareInterface, ParameterAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    use ParameterAwareTrait;
}
