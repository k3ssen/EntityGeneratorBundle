<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Traits;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Entity;

interface MetaTraitInterface
{
    public function __construct(Entity $metaEntity);

    public function getName(): string;

    public function getNamespace(): string;
}