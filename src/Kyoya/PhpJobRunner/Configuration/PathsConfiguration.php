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
            ->scalarNode('cache')->defaultValue('%root_dir%/app/cache/%env%')->end()
            ->scalarNode('config')->defaultValue('%root_dir%/app/config')->end()
            ->scalarNode('workflows')->defaultValue('%root_dir%/app/workflows')->end()
            ->scalarNode('log')->defaultValue('%root_dir%/app/logs')->end()
            ->end();

        return $treeBuilder;
    }
}
