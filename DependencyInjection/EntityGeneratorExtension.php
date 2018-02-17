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

        foreach ($config as $key => $value) {
            $container->setParameter('entity_generator.'.$key, $value);
        }

        $attributes = array_merge_recursive(
            $container->getParameter('default_attributes'),
            $container->getParameter('entity_generator.attributes')
        );
        $container->setParameter('entity_generator.attributes', $attributes);
    }
}
