<?php
namespace Kyoya\PhpJobRunner\DependencyInjection;

use Kyoya\PhpJobRunner\Configuration\YamlFileLoader;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * @var array
     */
    private $additionalParameters = array();

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function addParameter($key, $value)
    {
        $this->additionalParameters[$key] = $value;
    }

    /**
     * @param string $cacheFile
     * @param bool   $isDebug
     *
     * @return ContainerInterface
     */
    public function loadContainer($cacheFile, $isDebug = false)
    {
        $cacheDir             = "{$this->rootDir}/app/cache";
        $configDir            = "{$this->rootDir}/app/config";
        $realCacheFile        = "{$cacheDir}/$cacheFile";
        $containerConfigCache = new ConfigCache($realCacheFile, $isDebug);

        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();

            $containerBuilder->setParameter('root_dir', $this->rootDir);

            $locator        = new FileLocator($configDir);
            $loaderResolver = new LoaderResolver(array(new YamlFileLoader($containerBuilder, $locator)));
            $delegateLoader = new DelegatingLoader($loaderResolver);
            $delegateLoader->load('config.yml');

            $containerBuilder->setParameter('debug', $isDebug);

            foreach ($this->additionalParameters as $key => $value) {
                $containerBuilder->setParameter($key, $value);
            }

            $loader = new XmlFileLoader($containerBuilder, new FileLocator($configDir));
            $loader->load("services.xml");

            $loader = new XmlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../Resources/config'));
            $loader->load("services.xml");

            $containerBuilder->setAlias(
                'state_storage',
                $containerBuilder->getParameter('state_storage.provider')
            );

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
