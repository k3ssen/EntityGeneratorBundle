<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Inflector\Inflector;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaPropertyFactory;

class OneToOneProperty extends AbstractRelationshipProperty
{
    public function __construct(MetaEntity $metaEntity, ArrayCollection $metaAttributes, string $name)
    {
        parent::__construct($metaEntity, $metaAttributes, $name);
        $this->getMetaAttribute('inversedBy')->setDefaultValue(lcfirst($metaEntity->getName()));
    }

    public function getAnnotationLines(): array
    {
        $oneToOneOptions = 'targetEntity="'.$this->getTargetEntity()->getFullClassName().'"';
        $oneToOneOptions .= $this->getInversedBy() ? ', inversedBy="'.$this->getInversedBy().'"' : '';
        $oneToOneOptions .= $this->getMappedBy() ? ', mappedBy="'.$this->getMappedBy().'"' : '';
        $oneToOneOptions .= ', cascade={"persist"}';

        $annotationLines =  [
            '@ORM\OneToOne('.$oneToOneOptions.')',
        ];
        if (!$this->getMappedBy()) {
            $joinColumnOptions = 'name="' . Inflector::tableize($this->getName()) . ($this->getReferencedColumnName() === 'id' ? '_id"' : '');
            $joinColumnOptions .= ', referencedColumnName="'.$this->getReferencedColumnName().'"';
            $joinColumnOptions .= $this->isNullable() ? ', nullable=true' : ', nullable=false';
            $annotationLines[] = '@ORM\JoinColumn('.$joinColumnOptions.')';
        }
        return array_merge($annotationLines, parent::getAnnotationLines());
    }

    public function getOrmType(): string
    {
        return MetaPropertyFactory::ONE_TO_ONE;
    }
}
