<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class BigIntProperty extends IntegerProperty
{
    public function setLength(?int $length)
    {
        $this->length = $length;
    }

    public function getOrmType(): string
    {
        return Type::BIGINT;
    }
}
