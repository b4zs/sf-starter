<?php

namespace Core\ChunkUploadBundle\DependencyInjection;

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('core_chunk_upload');

        $rootNode
            ->children()
                ->arrayNode('extension_css_classes')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('extension_default_css_class')->defaultValue('glyphicon glyphicon-file')->end()
                ->scalarNode('media_class')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
