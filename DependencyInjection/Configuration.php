<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('entity_generator');
        $rootNode
            ->children()
                ->arrayNode('traits')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('override_skeleton_path')
                    ->defaultNull()
                ->end()
                ->booleanNode('auto_generate_repository')
                    ->defaultTrue()
                ->end()
                ->arrayNode('attributes')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('meta_properties')->scalarPrototype()->end()->end()
                            ->enumNode('type')
                                ->values(['string', 'int', 'bool', 'object', 'array'])
                            ->end()
                            ->booleanNode('default')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
