<?php

namespace Kyoya\PhpJobRunner\StorageProvider;

use Kyoya\PhpJobRunner\ParameterBag\ParameterBagInterface;

interface ProviderInterface
{
    /**
     * Puts the parameter bag to a persistent storage.
     *
     * @param string                $identifier
     * @param ParameterBagInterface $parameterBag
     *
     * @return void
     */
    public function persist($identifier, ParameterBagInterface $parameterBag);

    /**
     * Loads the parameter bag from a persistent storage.
     *
     * @param string                $identifier
     * @param ParameterBagInterface $parameterBag
     *
     * @return void
     */
    public function load($identifier, ParameterBagInterface $parameterBag);
}
