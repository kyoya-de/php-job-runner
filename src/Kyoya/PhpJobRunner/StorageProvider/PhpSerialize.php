<?php

namespace Kyoya\PhpJobRunner\StorageProvider;

use Kyoya\PhpJobRunner\ParameterBag\ParameterBagInterface;

class PhpSerialize implements ProviderInterface
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @param string $providerConfig
     */
    public function __construct($providerConfig)
    {
        $this->tempDir = $providerConfig['tempPath'];
    }

    /**
     * Puts the parameter bag to a persistent storage.
     *
     * @param string                $identifier
     * @param ParameterBagInterface $parameterBag
     *
     * @throws IOException If the state file isn't writable.
     *
     * @return void
     */
    public function persist($identifier, ParameterBagInterface $parameterBag)
    {
        $stateFilename = $this->getStateFilename($identifier);
        if (!touch($stateFilename) || (file_exists($stateFilename) && !is_writable($stateFilename))) {
            throw new IOException("State isn't writable!");
        }

        file_put_contents($stateFilename, serialize($parameterBag));
    }

    /**
     * Loads the parameter bag from a persistent storage.
     *
     * @param string                $identifier
     * @param ParameterBagInterface $parameterBag
     *
     * @throws IOException If state file doesn't exist.
     *
     * @return void
     */
    public function load($identifier, ParameterBagInterface $parameterBag)
    {
        $stateFilename = $this->getStateFilename($identifier);
        if (!file_exists($stateFilename)) {
            throw new IOException("State file {$stateFilename} doesn't exist!");
        }

        $stateFileContent = file_get_contents($stateFilename);

        $parameterBag->add(unserialize($stateFileContent));
    }

    /**
     * @param $identifier
     *
     * @return string
     */
    protected function getStateFilename($identifier)
    {
        return "{$this->tempDir}/{$identifier}.state";
    }
}
