<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\DependencyInjection;

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
                ->booleanNode('use_default_validations')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_bundle')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_sub_dir')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_display_field')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_id')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_nullable')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_unique')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_length')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_precision')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_scale')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_target_entity')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_inversed_by')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_mapped_by')
                    ->defaultTrue()
                ->end()
                ->booleanNode('ask_validations')
                    ->defaultTrue()
                ->end()
                ->booleanNode('show_all_validation_options')
                    ->defaultTrue()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
