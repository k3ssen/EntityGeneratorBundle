<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\Common\Inflector\Inflector;

class OneToOneProperty extends AbstractRelationshipProperty
{
    public function getReturnType(): string
    {
        return $this->getTargetEntity();
    }

    public function getAnnotationLines(): array
    {
        $oneToOneOptions = 'targetEntity="'.$this->getTargetEntityFullClassName().'"';
        $oneToOneOptions .= $this->getInversedBy() ? ', inversedBy="'.$this->getInversedBy().'"' : '';
        $oneToOneOptions .= $this->getMappedBy() ? ', mappedBy="'.$this->getMappedBy().'"' : '';
        $oneToOneOptions .= ', cascade={"persist"}';

        $annotationLines =  [
            '@ORM\OneToOne('.$oneToOneOptions.')',
        ];
        if (!$this->getMappedBy()) {
            //TODO: we're assuming id now, but other columns are possible
            $joinColumnOptions = 'name="' . Inflector::tableize($this->getName()) . '_id"';
            $joinColumnOptions .= ', referencedColumnName="id"';
            $joinColumnOptions .= $this->isNullable() ? ', nullable=true' : ', nullable=false';
            $annotationLines[] = '@ORM\JoinColumn('.$joinColumnOptions.')';
        }

        return $annotationLines;
    }

    public function getOrmType(): string
    {
        return MetaPropertyFactory::ONE_TO_ONE;
    }
}
