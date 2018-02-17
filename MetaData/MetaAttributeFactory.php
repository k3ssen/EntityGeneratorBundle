<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

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
        if ($value = $attributeInfo['value'] ?? null) {
            if (!$type) {
                if (is_numeric($value)) {
                    $metaAttribute->setType('int');
                } elseif(is_bool($value)) {
                    $metaAttribute->setType('bool');
                } elseif(is_array($value)) {
                    $metaAttribute->setType('array');
                }
            }
            $metaAttribute->setValue($value);
        }
        return $metaAttribute;
    }
}