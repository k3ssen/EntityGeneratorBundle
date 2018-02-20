<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\DependencyInjection\Compiler;

use Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion\AttributeQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion\BasicAttributeQuestion;
use Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion\EntityQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Command\PropertyQuestion\PropertyQuestionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class QuestionPass implements CompilerPassInterface
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
                }
                if (is_subclass_of($definition->getClass(), PropertyQuestionInterface::class, true)) {
                    $priority = 0;
                    if (defined($definition->getClass().'::PRIORITY')) {
                        $priority = constant($definition->getClass().'::PRIORITY');
                    }
                    $definition->addTag('entity_generator.property_question', ['priority' => $priority]);
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