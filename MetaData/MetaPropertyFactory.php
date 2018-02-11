<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Type;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

class MetaPropertyFactory
{
    public const MANY_TO_MANY = 'ManyToMany';
    public const ONE_TO_MANY = 'OneToMany';
    public const MANY_TO_ONE = 'ManyToOne';
    public const ONE_TO_ONE = 'OneToOne';

    /** @var array */
    protected $attributes;

    /** @var MetaAttributeFactory */
    protected $metaAttributeFactory;

    public function __construct(array $attributes, MetaAttributeFactory $metaAttributeFactory)
    {
        $this->attributes = $attributes;
        $this->metaAttributeFactory = $metaAttributeFactory;
    }

    public function getTypes()
    {
        return [
            Type::STRING => Property\StringProperty::class,
            Type::INTEGER => Property\IntegerProperty::class,
            Type::SMALLINT => Property\SmallIntProperty::class,
            TYPE::BIGINT => Property\BigIntProperty::class,
            Type::DECIMAL => Property\DecimalProperty::class,
            Type::TEXT => Property\TextProperty::class,
            Type::DATE => Property\DateProperty::class,
            Type::TIME => Property\TimeProperty::class,
            Type::DATETIME => Property\DateTimeProperty::class,
            Type::BOOLEAN => Property\BooleanProperty::class,
            Type::SIMPLE_ARRAY => Property\SimpleArrayProperty::class,
            Type::JSON => Property\JsonProperty::class,
            TYPE::OBJECT => Property\ObjectProperty::class,
            static::MANY_TO_ONE => Property\ManyToOneProperty::class,
            static::ONE_TO_MANY => Property\OneToManyProperty::class,
            static::MANY_TO_MANY => Property\ManyToManyProperty::class,
            static::ONE_TO_ONE => Property\OneToOneProperty::class,
        ];
    }

    public static function getInversedType($type): string
    {
        switch ($type) {
            case static::MANY_TO_ONE:
                return static::ONE_TO_MANY;
            case static::ONE_TO_MANY:
                return static::MANY_TO_ONE;
            case static::MANY_TO_MANY:
                return static::MANY_TO_MANY;
            case static::ONE_TO_ONE:
                return static::ONE_TO_ONE;
            default:
                throw new \InvalidArgumentException(sprintf('Type "%s" has no inversed type.', $type));
        }
    }

    public function getAliasedTypeOptions(): array
    {
        return [
            'str' => Type::STRING,
            'int' => Type::INTEGER,
            'sint' => Type::SMALLINT,
            'bint' => TYPE::BIGINT,
            'dec' => Type::DECIMAL,
            'txt' => Type::TEXT,
            'date' => Type::DATE,
            'time' => Type::TIME,
            'dt' => Type::DATETIME,
            'bool' => Type::BOOLEAN,
            'sarr' => Type::SIMPLE_ARRAY,
            'json' => Type::JSON,
            'obj' => TYPE::OBJECT,
            'm2o' => static::MANY_TO_ONE,
            'o2m' => static::ONE_TO_MANY,
            'm2m' => static::MANY_TO_MANY,
            'o2o' => static::ONE_TO_ONE,
        ];
    }

    public function getMetaProperty(MetaEntity $metaEntity, string $type, string $name): ?Property\AbstractProperty
    {
        if (array_key_exists($type, $this->getTypes())) {
            /** @var Property\AbstractProperty $typeClass */
            $typeClass = $this->getTypes()[$type];
            /** @var MetaAttribute[] $metaAttributes */
            $metaAttributes = new ArrayCollection();

            foreach ($this->attributes as $attributeName => $attributeInfo) {
                $classes = $attributeInfo['meta_properties'] ?? [];
                if (is_string($classes)) {
                    $classes = [$classes];
                }
                if (empty($classes)) {
                    $metaAttributes->set($attributeName, $this->metaAttributeFactory->createMetaAttribute($attributeName, $attributeInfo));
                    continue;
                }
                foreach ($classes as $class) {
                    if (is_a($typeClass, $class, true)) {
                        $metaAttributes->set($attributeName, $this->metaAttributeFactory->createMetaAttribute($attributeName, $attributeInfo));
                    }
                }
            }
            $metaProperty = new $typeClass($metaEntity, $metaAttributes, $name);
            foreach ($metaAttributes as $metaAttribute) {
                $metaAttribute->setMetaProperty($metaProperty);
            }

            return $metaProperty;
        }
        return null;
    }
}