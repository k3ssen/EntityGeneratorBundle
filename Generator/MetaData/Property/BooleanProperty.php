<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class BooleanProperty extends AbstractPrimitiveProperty
{
    public function getReturnType(): string
    {
        return 'bool';
    }

    public function getOrmType(): string
    {
        return Type::BOOLEAN;
    }
}
