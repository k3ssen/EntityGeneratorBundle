<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Doctrine\Common\Inflector\Inflector;

class OneToManyProperty extends AbstractRelationshipProperty
{
    public function __construct(MetaEntity $metaEntity, string $name)
    {
        parent::__construct($metaEntity, $name);

        $this->setTargetEntity(ucfirst(Inflector::tableize($name)));
        $this->setMappedBy(lcfirst(Inflector::classify($metaEntity->getName())));

        $metaEntity->addUsage('Doctrine\Common\Collections\Collection');
        $metaEntity->addUsage('Doctrine\Common\Collections\ArrayCollection');
    }

    public function setInversedBy(string $inversedBy): AbstractRelationshipProperty
    {
        throw new \RuntimeException(sprintf('Cannot call setInversedBy on "%s"; A OneToMany property always is the inversed side', static::class));
    }

    public function setNullable(?bool $nullable)
    {
        if ($nullable === false) {
            throw new \BadMethodCallException('Setting nullable to false on ManyToMany is not possible.');
        }
        $this->nullable = $nullable;
        return $this;
    }

    public function getOrphanRemoval(): bool
    {
        //TODO: add setter
        return false;
    }

    public function getAnnotationLines(): array
    {
        $OneToManyOptions = 'targetEntity="'.$this->getTargetEntityFullClassName().'"';
        $OneToManyOptions .= ', mappedBy="'.$this->getMappedBy().'"';
        $OneToManyOptions .= $this->getOrphanRemoval() ? ', orphanRemoval=true' : '';
        //TODO: what about cascade delete?
        $OneToManyOptions .= ', cascade={"persist"}';

        return [
            '@ORM\OneToMany('.$OneToManyOptions.')',
        ];
    }

    public function getReturnType(): string
    {
        return 'Collection';
    }
}
