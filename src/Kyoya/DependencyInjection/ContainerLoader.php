<?php
namespace Kyoya\DependencyInjection;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ContainerLoader implements ContainerLoaderInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var \CachedContainer
     */
    private $container;

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @param string $cacheFile
     * @param bool   $isDebug
     *
     * @return \CachedContainer
     */
    public function loadContainer($cacheFile, $isDebug = false)
    {
        $realCacheFile        = "{$this->rootDir}/app/cache/$cacheFile";
        $containerConfigCache = new ConfigCache($realCacheFile, $isDebug);

        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();

            $loader = new XmlFileLoader($containerBuilder, new FileLocator("{$this->rootDir}/app/config/"));
            $loader->load("services.xml");

            $containerBuilder->compile();

            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(array('class' => 'CachedContainer')),
                $containerBuilder->getResources()
            );
        }

        require_once $realCacheFile;

        return $this->container = new \CachedContainer();
    }

    /**
     * @return \CachedContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

}
