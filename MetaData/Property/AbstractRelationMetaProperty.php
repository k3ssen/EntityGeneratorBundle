<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\Common\Collections\ArrayCollection;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;

abstract class AbstractRelationMetaProperty extends AbstractMetaProperty implements RelationMetaPropertyInterface
{
    public function __construct(MetaEntity $metaEntity, ArrayCollection $metaAttributes, string $name)
    {
        parent::__construct($metaEntity, $metaAttributes, $name);
        $this->getMetaAttribute('targetEntity')->setDefaultValue(new MetaEntity($metaEntity->getNamespace().'\\'.ucfirst($name)));
    }

    public function getTargetEntity(): ?MetaEntity
    {
        return $this->getAttribute('targetEntity');
    }

    public function setTargetEntity(MetaEntity $targetEntity)
    {
        $this->setAttribute('targetEntity', $targetEntity);
        if ($targetEntity->getNamespace() !== $this->getMetaEntity()->getNamespace()) {
            $this->getMetaEntity()->addUsage($targetEntity->getFullClassName());
        }
        return $this;
    }

    public function getReferencedColumnName(): ?string
    {
        return $this->getAttribute('referencedColumnName');
    }

    public function setReferencedColumnName(string $referencedColumnName)
    {
        return $this->setAttribute('referencedColumnName', $referencedColumnName);
    }

    public function getInversedBy(): ?string
    {
        return $this->hasAttribute('inversedBy') ? $this->getAttribute('inversedBy') : null;
    }

    public function setInversedBy(?string $inversedBy)
    {
        if ($this->getMappedBy()) {
            throw new \RuntimeException(sprintf('Cannot set inversedBy on property with name "%s"; The mappedBy has already been set', $this->getName()));
        }
        return $this->setAttribute('inversedBy', $inversedBy);
    }

    public function getMappedBy(): ?string
    {
        return $this->hasAttribute('mappedBy') ? $this->getAttribute('mappedBy') : null;
    }

    public function setMappedBy(?string $mappedBy)
    {
        if ($this->getInversedBy()) {
            throw new \RuntimeException(sprintf('Cannot set mappedBy on property with name "%s"; The inversedBy has already been set', $this->getName()));
        }
        return $this->setAttribute('mappedBy', $mappedBy);
    }

    public function getOrphanRemoval(): ?bool
    {
        return $this->getAttribute('orphanRemoval');
    }

    public function setOrphanRemoval(bool $orphanRemoval)
    {
        return $this->setAttribute('orphanRemoval', $orphanRemoval);
    }
}
