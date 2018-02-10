<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Inflector;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;

abstract class AbstractRelationshipProperty extends AbstractProperty
{
    public function __construct(MetaEntity $metaEntity, ArrayCollection $metaAttributes, string $name)
    {
        parent::__construct($metaEntity, $metaAttributes, $name);
        $this->getMetaAttribute('targetEntity')->setDefaultValue(Inflector::classify($name));
    }

    public function getTargetEntity(): ?string
    {
        return $this->getAttribute('targetEntity');
    }

    public function setTargetEntity(string $targetEntity): self
    {
        return $this->setAttribute('targetEntity', $targetEntity);
    }

    public function getTargetEntityNamespace(): ?string
    {
        $metaAttribute = $this->getMetaAttributes()->get('targetEntityNamespace');
        if ($metaAttribute) {
            return $metaAttribute->getValue();
        }
        return $this->getMetaEntity()->getNamespace();
    }

    public function getTargetEntityFullClassName(): string
    {
        return $this->getTargetEntityNamespace().'\\'.$this->getTargetEntity();
    }

    public function setTargetEntityNamespace(string $targetEntityNamespace): self
    {
        $this->setAttribute('targetEntityNamespace', $targetEntityNamespace);
        if ($targetEntityNamespace !== $this->getMetaEntity()->getNamespace()) {
            $this->getMetaEntity()->addUsage($this->getTargetEntityFullClassName());
        }
        return $this;
    }

    public function getReferencedColumnName(): ?string
    {
        return $this->getAttribute('referencedColumnName');
    }

    public function setReferencedColumnName(string $referencedColumnName): self
    {
        return $this->setAttribute('referencedColumnName', $referencedColumnName);
    }

    public function getInversedBy(): ?string
    {
        return $this->hasAttribute('inversedBy') ? $this->getAttribute('inversedBy') : null;
    }

    public function setInversedBy(?string $inversedBy): self
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

    public function setMappedBy(?string $mappedBy): self
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

    public function setOrphanRemoval(bool $orphanRemoval): self
    {
        return $this->setAttribute('orphanRemoval', $orphanRemoval);
    }
}