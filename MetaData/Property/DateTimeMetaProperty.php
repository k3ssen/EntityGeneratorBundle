<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class DateTimeMetaProperty extends AbstractPrimitiveMetaProperty
{
    public const ORM_TYPE = Type::DATETIME;
    public const RETURN_TYPE = '\DateTime';
    public const ORM_TYPE_ALIAS = 'dt';
}
