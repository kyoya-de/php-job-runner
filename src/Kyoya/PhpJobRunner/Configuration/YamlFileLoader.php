<?php

namespace Kyoya\PhpJobRunner\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Yaml\Yaml;

class YamlFileLoader extends FileLoader
{
    /**
     * Loads a resource.
     *
     * @param mixed       $resource The resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @throws \Exception If something went wrong
     */
    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);

        $config = Yaml::parse(file_get_contents($path));

        $processor        = new Processor();
        $stateStorageConf = new StateStorageConfiguration();
        $pathConf         = new PathsConfiguration();

        $stateStorage = $processor->processConfiguration(
            $stateStorageConf,
            array('state_storage' => $config['state_storage'])
        );

        foreach ($stateStorage as $key => $value) {
            $this->container->setParameter("state_storage.{$key}", $value);
        }

        $paths = $processor->processConfiguration($pathConf, array('paths' => $config['paths']));
        foreach ($paths as $key => $value) {
            $this->container->setParameter("{$key}_dir", $value);
        }

        try {
            $this->import('config_local.yml');
        } catch (FileLoaderLoadException $e) {
        }
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
