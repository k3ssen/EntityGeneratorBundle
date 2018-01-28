<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class ObjectProperty extends AbstractPrimitiveProperty
{
    public function getReturnType(): string
    {
        //TODO: what to use here? it's very possible that stdClass isn't correct.
        return '\stdClass';
    }

    public function getOrmType(): string
    {
        return Type::OBJECT;
    }
}
