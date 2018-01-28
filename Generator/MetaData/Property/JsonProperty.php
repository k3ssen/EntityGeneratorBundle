<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class JsonProperty extends AbstractPrimitiveProperty
{
    public function getReturnType(): string
    {
        return 'array';
    }

    public function getOrmType(): string
    {
        return Type::JSON;
    }
}
