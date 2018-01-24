<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\EntityAnnotation;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;

interface AnnotationInterface
{
    public function __construct(MetaEntity $metaEntity);

    public function getNamespace(): string;

    public function getAnnotationProperties(): ?array;
}