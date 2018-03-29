<?php
namespace EB78\CustomI18nRouterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/bundles/extension.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('custom_router');
        $rootNode
            ->children()
            ->arrayNode('classes')
            ->addDefaultsIfNotSet()
            ->canBeUnset()
            ->end()
            ->end();
        return $treeBuilder;
    }
}
