<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Traits;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;

interface MetaTraitInterface
{
    public function __construct(MetaEntity $metaEntity);

    public function getName(): string;

    public function getNamespace(): string;
}