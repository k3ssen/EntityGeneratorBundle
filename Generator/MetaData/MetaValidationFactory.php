<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;

class MetaValidationFactory
{
    public function createMetaValidation(AbstractProperty $metaProperty, string $className, array $options = []): MetaValidation
    {
        return new MetaValidation($metaProperty, $className, $options);
    }
}