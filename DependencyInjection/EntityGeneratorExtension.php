<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class EntityGeneratorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../config'));
        $loader->load('services.yaml');

        $container->setParameter('entity_generator.default_bundle', $config['default_bundle'] ?? null);
        $container->setParameter('entity_generator.traits', $config['traits'] ?? []);
//        $container->setParameter('entity_generator.enable_datatables', $config['enable_datatables'] ?? true);
//        $container->setParameter('entity_generator.enable_voters', $config['enable_voters'] ?? true);

//        // CRUD
//        $container->setParameter('entity_generator.crud.datatables', isset($config['crud']['datatables']));
//
//        foreach ($config['class'] as $behaviour => $class) {
//            $container->setParameter(sprintf('entity_generator.behaviour.%s.class', $behaviour), $class);
//        }
    }
}
