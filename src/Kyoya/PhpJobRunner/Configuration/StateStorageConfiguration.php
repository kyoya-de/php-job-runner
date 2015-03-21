<?php

namespace Kyoya\PhpJobRunner\Configuration;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class StateStorageConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('state_storage');
        $rootNode
            ->children()
            ->scalarNode('provider')->isRequired()->end()
            ->arrayNode('provider_config')->prototype('scalar')->end()
            ->end();

        return $treeBuilder;
    }
}
