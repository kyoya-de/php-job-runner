<?php

namespace Kyoya\PhpJobRunner\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ContainerAwareCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;
}
