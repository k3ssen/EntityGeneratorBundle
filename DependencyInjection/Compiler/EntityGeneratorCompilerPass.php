<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\DependencyInjection\Compiler;

use Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion\AttributeQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion\BasicAttributeQuestion;
use Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion\EntityQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Command\PropertyQuestion\PropertyQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttributeFactory;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttributeInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityFactory;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaPropertyFactory;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaValidationFactory;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaValidationInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\MetaPropertyInterface;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EntityGeneratorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            if ($definition->getClass() && class_exists($definition->getClass(), false)) {
                if (is_subclass_of($definition->getClass(), EntityQuestionInterface::class, true)) {
                    $attributes = [];
                    if (defined($definition->getClass().'::PRIORITY')) {
                        $attributes['priority'] = constant($definition->getClass().'::PRIORITY');
                    }
                    $definition->addTag('entity_generator.entity_question', $attributes);
                    continue;
                }
                if (is_subclass_of($definition->getClass(), PropertyQuestionInterface::class, true)) {
                    $priority = 0;
                    if (defined($definition->getClass().'::PRIORITY')) {
                        $priority = constant($definition->getClass().'::PRIORITY');
                    }
                    $definition->addTag('entity_generator.property_question', ['priority' => $priority]);
                    continue;
                }

                if (is_subclass_of($definition->getClass(), MetaPropertyInterface::class, true)) {
                    $container->getDefinition(MetaPropertyFactory::class)->addMethodCall('addMetaPropertyClass', [$definition->getClass()]);
                    continue;
                }
                if (is_subclass_of($definition->getClass(), MetaEntityInterface::class, true)) {
                    $container->getDefinition(MetaEntityFactory::class)->addMethodCall('setMetaEntityClass', [$definition->getClass()]);
                    continue;
                }
                if (is_subclass_of($definition->getClass(), MetaAttributeInterface::class, true)) {
                    $container->getDefinition(MetaAttributeFactory::class)->addMethodCall('setMetaAttributeClass', [$definition->getClass()]);
                    continue;
                }
                if (is_subclass_of($definition->getClass(), MetaValidationInterface::class, true)) {
                    $container->getDefinition(MetaValidationFactory::class)->addMethodCall('setMetaValidationClass', [$definition->getClass()]);
                    continue;
                }
            }
        }

        foreach ($container->getParameter('entity_generator.attributes') as $attributeName => $attributeInfo) {
            if(array_key_exists('question', $attributeInfo)) {
                $serviceId = $attributeInfo['question'] ?: null;
                if (!$serviceId) {
                    continue;
                }
            } else {
                $serviceId = BasicAttributeQuestion::class;
            }
            $definition = $container->getDefinition($serviceId);
            if (!is_subclass_of($definition->getClass(), AttributeQuestionInterface::class,true)) {
                throw new InvalidDefinitionException(sprintf('Question service for attribute must implement "%s"; got "%s"', AttributeQuestionInterface::class, $definition->getClass()));
            }
            $definition->addMethodCall('addAttribute', [$attributeName, $attributeInfo]);
            if ($definition->hasTag('entity_generator.attribute_question') === false) {
                $definition->addTag('entity_generator.attribute_question');
            }
        }
    }
}