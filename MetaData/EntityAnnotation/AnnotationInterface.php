<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\MetaData\EntityAnnotation;


use K3ssen\EntityGeneratorBundle\MetaData\MetaEntityInterface;

interface AnnotationInterface
{
    public function __construct(MetaEntityInterface $metaEntity);

    public function getNamespace(): string;

    public function getAnnotationProperties(): ?array;
}