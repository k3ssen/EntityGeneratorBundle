<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Kevin3ssen\EntityGeneratorBundle\MetaData\EntityAnnotation\AnnotationInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\EntityAnnotation\OrmEntityAnnotation;
use Kevin3ssen\EntityGeneratorBundle\MetaData\EntityAnnotation\OrmTableAnnotation;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractPrimitiveProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Traits\MetaTraitInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Collections\ArrayCollection;

class MetaEntity
{
    protected $name;

    protected $namespace;

    protected $bundle;

    protected $subDir;

    protected $usages = [];

    protected $entityAnnotations = [];

    protected $traits = [];

    /** @var array */
    protected $properties = [];

    /** @var AbstractPrimitiveProperty */
    protected $displayProperty;

    protected $customRepository = true;

    public function __construct(string $name, $bundle = null, $subDir = null)
    {
        $this->setName($name);

        $this->setBundle($bundle);
        $this->setSubDir($subDir);

        $this->properties = new ArrayCollection();

        $this->addEntityAnnotation(new OrmTableAnnotation($this));
        $this->addEntityAnnotation(new OrmEntityAnnotation($this));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = Inflector::classify($name);
        return $this;
    }

    public function getBundle(): ?string
    {
        return $this->bundle;
    }

    protected function setNamespace()
    {
        if ($bundle = $this->getBundle()) {
            $this->namespace = $bundle.'\Entity';
        } else {
            $this->namespace = 'App\Entity';
        }
        if ($subDir = $this->getSubDir()) {
            $this->namespace .= '\\'.$subDir;
        }
    }

    public function setBundle(?string $bundle)
    {
        $this->bundle = $bundle;
        $this->setNamespace();
        return $this;
    }

    public function getSubDir(): ?string
    {
        return $this->subDir;
    }

    public function setSubDir(?string $subDir)
    {
        $this->subDir = $subDir;
        $this->setNamespace();
        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getUsages(): ?array
    {
        return $this->usages;
    }

    public function addUsage($namespace, $alias = null)
    {
        $this->usages[$namespace] = $alias;
    }

    /**
     * @return MetaTraitInterface[]
     */
    public function getTraits(): ?array
    {
        return $this->traits;
    }

    public function addTrait(MetaTraitInterface $trait)
    {
        $this->traits[] = $trait;
    }

    public function getTableName(): ?string
    {
        return Inflector::pluralize(Inflector::tableize($this->getName()));
    }

    public function getRepositoryFullClassName(): ?string
    {
        return str_replace('Entity', 'Repository', $this->getNamespace()) . '\\' . $this->getName() . 'Repository';
    }

    public function getRepositoryNamespace(): ?string
    {
        return str_replace('Entity', 'Repository', $this->getNamespace());
    }

    /**
     * @return AnnotationInterface[]
     */
    public function getEntityAnnotations(): ?array
    {
        return $this->entityAnnotations;
    }

    public function addEntityAnnotation(AnnotationInterface $entityAnnotation)
    {
        $this->entityAnnotations[] = $entityAnnotation;
    }

    /**
     * @return AbstractProperty[]|ArrayCollection
     */
    public function getProperties(): ArrayCollection
    {
        return $this->properties;
    }

    public function addProperty(AbstractProperty $property)
    {
        if (!$this->getProperties()->contains($property)) {
            $this->getProperties()->add($property);
            $property->setMetaEntity($this);
        }
        return $this;
    }

    public function removeProperty(AbstractProperty $property)
    {
        if ($this->getProperties()->contains($property)) {
            $this->getProperties()->removeElement($property);
        }
        return $this;
    }

    public function getDisplayProperty(): ?AbstractPrimitiveProperty
    {
        return $this->displayProperty;
    }

    public function setDisplayProperty(?AbstractPrimitiveProperty $displayProperty)
    {
        if ($displayProperty && !$this->getProperties()->contains($displayProperty)) {
            throw new \RuntimeException(sprintf('Cannot set property %s as display-property; This property hasn\'t been added to this entity yet', $displayProperty));
        }
        $this->displayProperty = $displayProperty;
    }

    public function getIdProperty(): ?AbstractPrimitiveProperty
    {
        foreach ($this->getProperties() as $property) {
            if ($property instanceof  AbstractPrimitiveProperty && $property->isId()) {
                return $property;
            }
        }
        return null;
    }

    public function hasCustomRepository(): bool
    {
        return $this->customRepository;
    }

    public function setUseCustomRepository(bool $customRepository): self
    {
        $this->customRepository = $customRepository;
        return $this;
    }


    public function __toString()
    {
        return $this->getName();
    }
}