<?php

namespace Sfynx\RestClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sfynx_rest_client');

        $rootNode
            ->children()
                ->scalarNode('http_cacher')->defaultNull()->end()
                ->booleanNode('log_enabled')->defaultFalse()->end()
                ->arrayNode('api')
                    ->useAttributeAsKey('')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('endpoint_root')->defaultNull()->end()
                            ->scalarNode('circuit_breaker')->defaultNull()->end()
                            ->scalarNode('security_token')->defaultNull()->end()
                            ->booleanNode('cache_enabled')->defaultTrue()->end()
                            ->arrayNode('client')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('service')
                                        ->defaultValue('sfynx_rest_client.api')
                                    ->end()
                                    ->scalarNode('implementor')
                                        ->defaultValue('sfynx_rest_client.api_implementor')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
