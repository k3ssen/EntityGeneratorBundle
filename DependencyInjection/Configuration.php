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
                ->booleanNode('auto_generate_repository')
                    ->defaultTrue()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
