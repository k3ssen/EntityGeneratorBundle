<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class SimpleArrayProperty extends AbstractPrimitiveProperty
{
    public function getReturnType(): string
    {
        return 'array';
    }

    public function getOrmType(): string
    {
        return Type::SIMPLE_ARRAY;
    }
}
