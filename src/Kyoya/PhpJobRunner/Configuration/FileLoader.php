<?php

namespace Kyoya\PhpJobRunner\Configuration;


use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FileLoader extends \Symfony\Component\Config\Loader\FileLoader
{
    use ContainerAwareTrait;

    public function __construct(ContainerInterface $container, FileLocatorInterface $locator)
    {
        $this->container = $container;

        parent::__construct($locator);
    }
}
