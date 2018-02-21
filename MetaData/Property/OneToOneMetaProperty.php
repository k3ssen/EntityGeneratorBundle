<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Inflector\Inflector;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaPropertyFactory;

class OneToOneMetaProperty extends AbstractRelationMetaProperty implements OneToOneMetaPropertyInterface
{
    public const ORM_TYPE_ALIAS = 'o2o';
    public const RETURN_TYPE = '\stdClass'; //Note that this class is an exception in which we actually want to return the targetEntity as returnType

    public function __construct(MetaEntityInterface $metaEntity, ArrayCollection $metaAttributes, string $name)
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
}
