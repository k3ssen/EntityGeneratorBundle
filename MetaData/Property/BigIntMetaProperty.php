<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class BigIntMetaProperty extends AbstractPrimitiveMetaProperty implements BigIntMetaPropertyInterface
{
    use LengthTrait;

    public const ORM_TYPE_ALIAS = 'bint';
    public const ORM_TYPE = Type::BIGINT;
    public const RETURN_TYPE = 'int';
}