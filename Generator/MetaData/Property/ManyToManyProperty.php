<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Doctrine\Common\Inflector\Inflector;

class ManyToManyProperty extends AbstractRelationshipProperty
{
    public function __construct(MetaEntity $metaEntity, string $name)
    {
        parent::__construct($metaEntity, $name);

        $this->setTargetEntity(ucfirst(Inflector::pluralize($name)));

        $metaEntity->addUsage('Doctrine\Common\Collections\Collection');
        $metaEntity->addUsage('Doctrine\Common\Collections\ArrayCollection');
    }

    public function setNullable(?bool $nullable)
    {
        if ($nullable === false) {
            throw new \BadMethodCallException('Setting nullable to false on ManyToMany is not possible.');
        }
        $this->nullable = $nullable;
        return $this;
    }

    public function getReturnType(): string
    {
        return 'Collection';
    }

    public function getOrphanRemoval(): bool
    {
        //TODO: add setter
        return false;
    }

    public function getAnnotationLines(): array
    {
        $manyToManyOptions = 'targetEntity="'.$this->getTargetEntityFullClassName().'"';
        $manyToManyOptions .= $this->getInversedBy() ? ', inversedBy="'.$this->getInversedBy().'"' : '';
        $manyToManyOptions .= $this->getMappedBy() ? ', mappedBy="'.$this->getMappedBy().'"' : '';
        $manyToManyOptions .= $this->getOrphanRemoval() ? ', orphanRemoval=true' : '';
        $manyToManyOptions .= ', cascade={"persist"}';

        $annotationLines =  [
            '@ORM\ManyToMany('.$manyToManyOptions.')',
        ];

        if (!$this->getMappedBy()) {
            $tableName = Inflector::pluralize(Inflector::tableize($this->getName())).'_'.Inflector::pluralize(Inflector::tableize($this->getTargetEntity()));
            $annotationLines[] = '@ORM\JoinTable(name="'.$tableName.'",';
            $annotationLines[] = '  joinColumns={';
            $annotationLines[] = '    @ORM\JoinColumn(name="'.Inflector::tableize($this->getName()).'_id", referencedColumnName="id", onDelete="CASCADE")';
            $annotationLines[] = '  }';
            $annotationLines[] = '  inverseJoinColumns={';
            $annotationLines[] = '    @ORM\JoinColumn(name="'.Inflector::tableize($this->getTargetEntity()).'_id", referencedColumnName="id", onDelete="CASCADE")';
            $annotationLines[] = '  }';
        }

        return $annotationLines;
    }
}
