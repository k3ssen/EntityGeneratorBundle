<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\DBAL\Types\Type;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;

class MetaPropertyFactory
{
    public const MANY_TO_MANY = 'ManyToMany';
    public const ONE_TO_MANY = 'OneToMany';
    public const MANY_TO_ONE = 'ManyToOne';
    public const ONE_TO_ONE = 'OneToOne';

    public function getTypes()
    {
        return [
            Type::STRING => StringProperty::class,
            Type::INTEGER => IntegerProperty::class,
            Type::SMALLINT => SmallIntProperty::class,
            TYPE::BIGINT => BigIntProperty::class,
            Type::DECIMAL => DecimalProperty::class,
            Type::TEXT => TextProperty::class,
            Type::DATE => DateProperty::class,
            Type::TIME => TimeProperty::class,
            Type::DATETIME => DateTimeProperty::class,
            Type::BOOLEAN => BooleanProperty::class,
            Type::SIMPLE_ARRAY => SimpleArrayProperty::class,
            Type::JSON => JsonProperty::class,
            TYPE::OBJECT => ObjectProperty::class,
            static::MANY_TO_ONE => ManyToOneProperty::class,
            static::ONE_TO_MANY => OneToManyProperty::class,
            static::MANY_TO_MANY => ManyToManyProperty::class,
            static::ONE_TO_ONE => OneToOneProperty::class,
        ];
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

    public function getMetaPropertyByType(MetaEntity $metaEntity, string $type, string $name): ?AbstractProperty
    {
        if (array_key_exists($type, $this->getTypes())) {
            /** @var AbstractProperty $typeClass */
            $typeClass = $this->getTypes()[$type];
            return new $typeClass($metaEntity, $name);
        }
        return null;
    }
}