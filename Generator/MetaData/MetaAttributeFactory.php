<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\AttributeQuestionInterface;

class MetaAttributeFactory
{
    public function createMetaAttribute(string $name, array $attributeInfo): MetaAttribute
    {
        $metaAttribute = new MetaAttribute($name);
        if ($type = $attributeInfo['type'] ?? null) {
            $metaAttribute->setType((string) $type);
        }
        if ($nullable = $attributeInfo['nullable'] ?? null) {
            $metaAttribute->setNullable((bool) $nullable);
        }
        $defaultValue = $attributeInfo['default'] ?? null;
        if ($defaultValue !== null) {
            $metaAttribute->setDefaultValue($defaultValue);
        }
        if ($question = $attributeInfo['question'] ?? null) {
            $metaAttribute->setQuestion((string) $question);
        }
        if ($expression = $attributeInfo['validation'] ?? null) {
            $metaAttribute->setValidation((string) $expression);
        }
        if ($expression = $attributeInfo['condition'] ?? null) {
            $metaAttribute->setCondition((string) $expression);
        }
        if ($service = $attributeInfo['service'] ?? null) {
            if (!is_a($service, AttributeQuestionInterface::class, true)) {
                throw new \InvalidArgumentException(sprintf(
                    'Service "%s" cannot be set on attribute, because it does not implement "%s"',
                    $service,
                    AttributeQuestionInterface::class
                ));
            }
            $metaAttribute->setQuestionService((string) $service);
        }
        if ($value = $attributeInfo['value'] ?? null) {
            $metaAttribute->setValue((string) $value);
            if (!$type) {
                if (is_numeric($value)) {
                    $metaAttribute->setType('int');
                } elseif(is_bool($value)) {
                    $metaAttribute->setType('bool');
                }
            }
        }
        return $metaAttribute;
    }
}