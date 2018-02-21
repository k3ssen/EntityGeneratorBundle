<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class TextMetaProperty extends AbstractPrimitiveMetaProperty implements TextMetaPropertyInterface
{
    public const ORM_TYPE = Type::TEXT;
    public const ORM_TYPE_ALIAS = 'txt';
    public const RETURN_TYPE = 'string';
}
