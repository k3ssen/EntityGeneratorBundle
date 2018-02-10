<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class DateTimeProperty extends AbstractPrimitiveProperty
{
    public function getReturnType(): string
    {
        return '\DateTime';
    }

    public function getOrmType(): string
    {
        return Type::DATETIME;
    }
}
