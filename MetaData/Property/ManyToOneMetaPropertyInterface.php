<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

interface ManyToOneMetaPropertyInterface extends RelationMetaPropertyInterface
{
    public const ORM_TYPE = 'ManyToOne';
}
