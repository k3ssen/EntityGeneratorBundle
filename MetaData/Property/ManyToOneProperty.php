<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Inflector\Inflector;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaPropertyFactory;

class ManyToOneProperty extends AbstractRelationshipProperty
{
    public function __construct(MetaEntity $metaEntity, ArrayCollection $metaAttributes, string $name)
    {
        parent::__construct($metaEntity, $metaAttributes, $name);
        $this->getMetaAttribute('inversedBy')->setDefaultValue(lcfirst($metaEntity->getName()));
    }

    public function setMappedBy(?string $mappedBy): AbstractRelationshipProperty
    {
        throw new \RuntimeException(sprintf('Cannot call setMappedBy on "%s"; A ManyToOne property always is the mapping side', static::class));
    }

    public function getReturnType(): string
    {
        return $this->getTargetEntity();
    }

    public function getAnnotationLines(): array
    {
        $manyToOneOptions = 'targetEntity="'.$this->getTargetEntityFullClassName().'"';
        $manyToOneOptions .= $this->getInversedBy() ? ', inversedBy="'.$this->getInversedBy().'"' : '';

        $joinColumnOptions = 'name="'. Inflector::tableize($this->getName()) . ($this->getReferencedColumnName() === 'id' ? '_id"': '');
        $joinColumnOptions .= ', referencedColumnName="'.$this->getReferencedColumnName().'"';
        $joinColumnOptions .= $this->isNullable() ? ', nullable=true' : ', nullable=false';

        $annotationLines = [
            '@ORM\ManyToOne('.$manyToOneOptions.', cascade={"persist"})',
            '@ORM\JoinColumn('.$joinColumnOptions.')',
        ];
        return array_merge($annotationLines, parent::getAnnotationLines());
    }

    public function getOrmType(): string
    {
        return MetaPropertyFactory::MANY_TO_ONE;
    }
}
