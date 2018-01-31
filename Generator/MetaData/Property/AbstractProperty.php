<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\Common\Util\Inflector;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaValidation;
use Symfony\Component\Validator\Constraints;

abstract class AbstractProperty
{
    /** @var string */
    protected $name;

    /** @var MetaEntity */
    protected $metaEntity;

    /** @var bool */
    protected $nullable;

    /** @var bool */
    protected $unique = false;

    /** @var MetaValidation[]|ArrayCollection */
    protected $validations = [];

    public function __construct(MetaEntity $metaEntity, string $name)
    {
        $this->setMetaEntity($metaEntity);
        $this->setName($name);
        $this->validations = new ArrayCollection();

        $metaEntity->addProperty($this);
    }

    public function getMetaEntity(): MetaEntity
    {
        return $this->metaEntity;
    }

    public function setMetaEntity(MetaEntity $metaEntity): self
    {
        $this->metaEntity = $metaEntity;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = lcfirst(Inflector::classify($name));
        return $this;
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    public function setNullable(?bool $nullable)
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function isUnique(): ?bool
    {
        return $this->unique;
    }

    public function setUnique(?bool $unique): self
    {
        $this->unique = $unique;
        return $this;
    }

    /** @return ArrayCollection|MetaValidation[] */
    public function getValidations(): ArrayCollection
    {
        return $this->validations;
    }

    public function setValidations(ArrayCollection $validations): self
    {
        $this->validations = $validations;
        return $this;
    }

    public function addValidation(MetaValidation $validation): self
    {
        if (!$this->getValidations()->contains($validation)) {
            $this->getValidations()->add($validation);
            $validation->setMetaProperty($this);
        }
        return $this;
    }

    public function removeValidation(MetaValidation $validation): self
    {
        if (!$this->getValidations()->contains($validation)) {
            $this->getValidations()->removeElement($validation);
        }
        return $this;
    }

    public function isHasValidation(): bool
    {
        return !$this->getValidations()->isEmpty();
    }

    abstract public function getReturnType(): string;

    abstract public function getOrmType(): string;

    abstract public function getAnnotationLines(): array;

    public function __toString()
    {
        return $this->getName();
    }
}
