<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class BigIntProperty extends IntegerProperty
{
    public function getOrmType(): string
    {
        return Type::BIGINT;
    }
}
