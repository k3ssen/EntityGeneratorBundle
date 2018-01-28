<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\Common\Util\Inflector;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;

abstract class AbstractRelationshipProperty extends AbstractProperty
{
    /** @var string */
    protected $targetEntity;

    /** @var string */
    protected $targetEntityNamespace;

    /** @var string */
    protected $referencedColumnName = 'id';

    /** @var string */
    protected $inversedBy;

    /** @var string */
    protected $mappedBy;

    /** @var bool */
    protected $orphanRemoval = true;

    public function __construct(MetaEntity $metaEntity, string $name)
    {
        parent::__construct($metaEntity, $name);
        $this->setTargetEntity(Inflector::classify($name));
    }

    public function getTargetEntity(): ?string
    {
        return $this->targetEntity;
    }

    public function setTargetEntity(string $targetEntity): self
    {
        $this->targetEntity = $targetEntity;
        return $this;
    }

    public function getTargetEntityNamespace(): ?string
    {
        return $this->targetEntityNamespace ?: $this->getMetaEntity()->getNamespace();
    }

    public function getTargetEntityFullClassName(): string
    {
        return $this->getTargetEntityNamespace().'\\'.$this->getTargetEntity();
    }

    public function setTargetEntityNamespace(string $targetEntityNamespace): self
    {
        $this->targetEntityNamespace = $targetEntityNamespace;
        if ($targetEntityNamespace !== $this->getMetaEntity()->getNamespace()) {
            $this->getMetaEntity()->addUsage($this->getTargetEntityFullClassName());
        }
        return $this;
    }

    public function getReferencedColumnName(): ?string
    {
        return $this->referencedColumnName;
    }

    public function setReferencedColumnName(string $referencedColumnName): self
    {
        $this->referencedColumnName = $referencedColumnName;
        return $this;
    }

    public function getInversedBy(): ?string
    {
        return $this->inversedBy;
    }

    public function setInversedBy(?string $inversedBy): self
    {
        $this->inversedBy = $inversedBy;
        return $this;
    }

    public function getMappedBy(): ?string
    {
        return $this->mappedBy;
    }

    public function setMappedBy(?string $mappedBy): self
    {
        if ($this->getInversedBy()) {
            throw new \RuntimeException(sprintf('Cannot set mappedBy on property with name "%s"; The inversedBy has already been set', $this->getName()));
        }
        $this->mappedBy = $mappedBy;
        return $this;
    }

    public function isOrphanRemoval(): ?bool
    {
        return $this->orphanRemoval;
    }

    public function setOrphanRemoval(bool $orphanRemoval): self
    {
        $this->orphanRemoval = $orphanRemoval;
        return $this;
    }
}