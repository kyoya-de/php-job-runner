<?php

namespace Kyoya\PhpJobRunner\Kernel;

use Kyoya\PhpJobRunner\Configuration\YamlFileLoader;
use Monolog\ErrorHandler;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

abstract class Kernel implements KernelInterface
{

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string
     */
    protected $name;

    public function __construct($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug       = (bool) $debug;
        $this->rootDir     = $this->getRootDir();
        $this->name        = $this->getName();

        $this->initializeContainer();
    }

    /**
     * Gets the name of the kernel.
     *
     * @return string The kernel name
     *
     * @api
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->rootDir));
        }

        return $this->name;
    }

    /**
     * Gets the environment.
     *
     * @return string The current environment
     *
     * @api
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Checks if debug mode is enabled.
     *
     * @return bool true if debug mode is enabled, false otherwise
     *
     * @api
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Gets the application root dir.
     *
     * @return string The application root dir
     *
     * @api
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $r             = new \ReflectionObject($this);
            $this->rootDir = str_replace('\\', '/', dirname($r->getFileName()));
        }

        return $this->rootDir;
    }

    /**
     * Gets the current container.
     *
     * @return ContainerInterface A ContainerInterface instance
     *
     * @api
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Gets the cache directory.
     *
     * @return string The cache directory
     *
     * @api
     */
    public function getCacheDir()
    {
        return $this->rootDir . '/cache/' . $this->environment;
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     *
     * @api
     */
    public function getLogDir()
    {
        return $this->rootDir . '/logs';
    }

    protected function initializeContainer()
    {
        $class = $this->getContainerClass();

        $cache = new ConfigCache($this->getCacheDir() . '/' . $class . '.php', $this->debug);
        if (!$cache->isFresh()) {
            $container = $this->getContainerBuilder();
            $container->compile();

            $dumper = new PhpDumper($container);
            $cache->write(
                $dumper->dump(array('class' => $class)),
                $container->getResources()
            );
        }

        require_once $cache;

        $this->container = new $class();
        $this->container->set('kernel', $this);
    }

    protected function getContainerBuilder()
    {
        $containerBuilder = new ContainerBuilder(new ParameterBag($this->getKernelParameters()));

        $locator        = new FileLocator($this->rootDir);
        $loaderResolver = new LoaderResolver(array(new YamlFileLoader($containerBuilder, $locator)));
        $delegateLoader = new DelegatingLoader($loaderResolver);
        $this->loadApplicationConfiguration($delegateLoader);

        $loader = new XmlFileLoader($containerBuilder, new FileLocator($this->rootDir));
        $this->loadContainerConfiguration($loader);

        $loader = new XmlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load("services.xml");

        $containerBuilder->setAlias(
            'state_storage',
            $containerBuilder->getParameter('state_storage.provider')
        );

        return $containerBuilder;
    }

    protected function getContainerClass()
    {
        return $this->name . ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }

    /**
     * Returns the kernel parameters.
     *
     * @return array An array of kernel parameters
     */
    protected function getKernelParameters()
    {
        return array_merge(
            array(
                'kernel.root_dir' => realpath($this->rootDir) ?: $this->rootDir,
                'kernel.environment' => $this->environment,
                'kernel.debug' => $this->debug,
                'kernel.name' => $this->name,
                'kernel.cache_dir' => realpath($this->getCacheDir()) ?: $this->getCacheDir(),
                'kernel.logs_dir' => realpath($this->getLogDir()) ?: $this->getLogDir(),
                'kernel.charset' => $this->getCharset(),
                'kernel.container_class' => $this->getContainerClass(),
            ),
            $this->getEnvParameters()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getCharset()
    {
        return 'UTF-8';
    }

    /**
     * Gets the environment parameters.
     *
     * Only the parameters starting with "PJR__" are considered.
     *
     * @return array An array of parameters
     */
    protected function getEnvParameters()
    {
        $parameters = array();
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'PJR__')) {
                $parameters[strtolower(str_replace('__', '.', substr($key, 9)))] = $value;
            }
        }

        return $parameters;
    }

    protected function registerErrorHandler()
    {
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->getContainer()->get('logger.error');
        ErrorHandler::register($logger);
    }
}
