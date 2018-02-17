<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\Common\Collections\ArrayCollection;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Doctrine\Common\Inflector\Inflector;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaPropertyFactory;

class ManyToManyProperty extends AbstractRelationshipProperty
{
    public function __construct(MetaEntity $metaEntity, ArrayCollection $metaAttributes, string $name)
    {
        parent::__construct($metaEntity, $metaAttributes, $name);
        $this->getMetaAttribute('targetEntity')->setDefaultValue(new MetaEntity($metaEntity->getNamespace().'\\'.Inflector::classify(Inflector::singularize($name))));
        $this->getMetaAttribute('inversedBy')->setDefaultValue(lcfirst($metaEntity->getName()));

        $metaEntity->addUsage('Doctrine\Common\Collections\Collection');
        $metaEntity->addUsage('Doctrine\Common\Collections\ArrayCollection');
    }

    public function getReturnType(): string
    {
        return 'Collection';
    }

    public function setNullable(?bool $nullable)
    {
        if ($nullable === false) {
            throw new \BadMethodCallException('Setting nullable to false on ManyToMany is not possible.');
        }
        return parent::setNullable($nullable);
    }

    public function getAnnotationLines(): array
    {
        $manyToManyOptions = 'targetEntity="'.$this->getTargetEntity()->getFullClassName().'"';
        $manyToManyOptions .= $this->getInversedBy() ? ', inversedBy="'.$this->getInversedBy().'"' : '';
        $manyToManyOptions .= $this->getMappedBy() ? ', mappedBy="'.$this->getMappedBy().'"' : '';
        $manyToManyOptions .= $this->getOrphanRemoval() ? ', orphanRemoval=true' : '';
        $manyToManyOptions .= ', cascade={"persist"}';

        $annotationLines =  [
            '@ORM\ManyToMany('.$manyToManyOptions.')',
        ];

        if (!$this->getMappedBy()) {
            $tableName = Inflector::pluralize(Inflector::tableize($this->getName())).'_'.Inflector::pluralize(Inflector::tableize($this->getTargetEntity()->getName()));
            $annotationLines[] = '@ORM\JoinTable(name="'.$tableName.'",';
            $annotationLines[] = '  joinColumns={';
            $annotationLines[] = '    @ORM\JoinColumn(name="'.Inflector::tableize($this->getName()).'_id", referencedColumnName="id", onDelete="CASCADE")';
            $annotationLines[] = '  }';
            $annotationLines[] = '  inverseJoinColumns={';
            $annotationLines[] = '    @ORM\JoinColumn(name="'.Inflector::tableize($this->getTargetEntity()->getName()).'_'.$this->getReferencedColumnName().'" , referencedColumnName="'.$this->getReferencedColumnName().'", onDelete="CASCADE")';
            $annotationLines[] = '  }';
        }

        return array_merge($annotationLines, parent::getAnnotationLines());
    }

    public function getOrmType(): string
    {
        return MetaPropertyFactory::MANY_TO_MANY;
    }
}
