<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;

interface RelationMetaPropertyInterface extends MetaPropertyInterface
{
    public function getTargetEntity(): ?MetaEntity;

    public function setTargetEntity(MetaEntity $targetEntity);

    public function getReferencedColumnName(): ?string;

    public function setReferencedColumnName(string $referencedColumnName);

    public function getInversedBy(): ?string;

    public function setInversedBy(?string $inversedBy);

    public function getMappedBy(): ?string;

    public function setMappedBy(?string $mappedBy);

    public function getOrphanRemoval(): ?bool;

    public function setOrphanRemoval(bool $orphanRemoval);
}
