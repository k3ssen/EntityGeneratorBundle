<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityInterface;

interface RelationMetaPropertyInterface extends MetaPropertyInterface
{
    public function getTargetEntity(): ?MetaEntityInterface;

    public function setTargetEntity(MetaEntityInterface $targetEntity);

    public function getReferencedColumnName(): ?string;

    public function setReferencedColumnName(string $referencedColumnName);

    public function getInversedBy(): ?string;

    public function setInversedBy(?string $inversedBy);

    public function getMappedBy(): ?string;

    public function setMappedBy(?string $mappedBy);

    public function getOrphanRemoval(): ?bool;

    public function setOrphanRemoval(bool $orphanRemoval);
}
