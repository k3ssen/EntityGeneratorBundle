<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\Common\Collections\ArrayCollection;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Doctrine\Common\Inflector\Inflector;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaPropertyFactory;

class OneToManyProperty extends AbstractRelationshipProperty
{
    public function __construct(MetaEntity $metaEntity, ArrayCollection $metaAttributes, string $name)
    {
        parent::__construct($metaEntity, $metaAttributes, $name);
        $this->setTargetEntity(Inflector::classify(Inflector::singularize($name)));
        $this->setMappedBy(lcfirst(Inflector::classify($metaEntity->getName())));
        $this->setOrphanRemoval(false);

        $metaEntity->addUsage('Doctrine\Common\Collections\Collection');
        $metaEntity->addUsage('Doctrine\Common\Collections\ArrayCollection');
    }

    public function setInversedBy(?string $inversedBy): AbstractRelationshipProperty
    {
        throw new \RuntimeException(sprintf('Cannot call setInversedBy on "%s"; A OneToMany property always is the inversed side', static::class));
    }

    public function setNullable(?bool $nullable)
    {
        if ($nullable === false) {
            throw new \BadMethodCallException('Setting nullable to false on OneToMany is not possible.');
        }
        return parent::setNullable($nullable);
    }

    public function getAnnotationLines(): array
    {
        $OneToManyOptions = 'targetEntity="'.$this->getTargetEntityFullClassName().'"';
        $OneToManyOptions .= ', mappedBy="'.$this->getMappedBy().'"';
        $OneToManyOptions .= $this->getOrphanRemoval() ? ', orphanRemoval=true' : '';
        //TODO: what about cascade delete?
        $OneToManyOptions .= ', cascade={"persist"}';

        $annotationLines = [
            '@ORM\OneToMany('.$OneToManyOptions.')',
        ];
        return array_merge($annotationLines, parent::getAnnotationLines());
    }

    public function getReturnType(): string
    {
        return 'Collection';
    }

    public function getOrmType(): string
    {
        return MetaPropertyFactory::ONE_TO_MANY;
    }
}
