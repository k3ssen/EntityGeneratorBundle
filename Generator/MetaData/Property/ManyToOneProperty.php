<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\Common\Inflector\Inflector;

class ManyToOneProperty extends AbstractRelationshipProperty
{
    public function setMappedBy(string $mappedBy): AbstractRelationshipProperty
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

        //TODO: we're assuming id now, but other columns are possible
        $joinColumnOptions = 'name="'. Inflector::tableize($this->getName()) . '_id"';
        $joinColumnOptions .= ', referencedColumnName="id"';
        $joinColumnOptions .= $this->isNullable() ? ', nullable=true' : ', nullable=false';

        return [
            '@ORM\ManyToOne('.$manyToOneOptions.', cascade={"persist"})',
            '@ORM\JoinColumn('.$joinColumnOptions.')',
        ];
    }
}
