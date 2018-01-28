<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class TextProperty extends AbstractPrimitiveProperty
{
    public function getReturnType(): string
    {
        return Type::STRING;
    }

    public function getOrmType(): string
    {
        return Type::TEXT;
    }
}
