<?php

namespace Kyoya\PhpJobRunner\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class PathsConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('paths');
        $rootNode
            ->children()
            ->scalarNode('cache')->defaultValue('%kernel.cache_dir%')->end()
            ->scalarNode('config')->defaultValue('%kernel.root_dir%/config')->end()
            ->scalarNode('workflows')->defaultValue('%kernel.root_dir%/workflows')->end()
            ->scalarNode('log')->defaultValue('%kernel.logs_dir%')->end()
            ->end();

        return $treeBuilder;
    }
}
