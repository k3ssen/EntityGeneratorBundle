<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        foreach ($config as $key => $value) {
            $container->setParameter('entity_generator.'.$key, $value);
        }

        $defaultAttributes = $container->getParameter('default_attributes');
        $configuredAttributes = $container->getParameter('entity_generator.attributes');
        $attributesMerged = array_merge_recursive($defaultAttributes, $configuredAttributes);
        $attributes = array_replace_recursive($defaultAttributes, $configuredAttributes);

        foreach ($attributes as $name => $attributeInfo) {
            //meta_properties can only be added, not replaced, so we use the merged-value instead
            $attributeInfo['meta_properties'] = $attributesMerged[$name]['meta_properties'];

            if (isset($defaultAttributes[$name]['type']) && $defaultAttributes[$name]['type'] !== $attributeInfo['type']) {
                throw new InvalidConfigurationException(sprintf('
                    Invalid configuration "entity_generator.attributes.%s"; "type" is set to "%s", but "%s" is required by EntityGeneratorBundle. Remove "type" from this configuration or change this its value to "%s"
                ', $name, $attributeInfo['type'], $defaultAttributes[$name]['type'], $defaultAttributes[$name]['type']));
            }
        }

        $container->setParameter('entity_generator.attributes', $attributes);
    }
}
