<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EntityFinder;
use Kevin3ssen\EntityGeneratorBundle\Generator\EntityGenerator;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;

class MetaEntityFactory
{
    /** @var EntityGenerator */
    protected $entityGenerator;

    /** @var MetaPropertyFactory */
    protected $metaPropertyFactory;

    /** @var bool */
    protected $autoGenerateRepository;

    public function __construct(
        ?bool $autoGenerateRepository,
        MetaPropertyFactory $metaPropertyFactory,
        EntityFinder $entityFinder
    )
    {
        $this->autoGenerateRepository = $autoGenerateRepository;
        $this->metaPropertyFactory = $metaPropertyFactory;
        //TODO: entityFinder is located in 'command' namespace, which isn't logical
        $this->entityFinder = $entityFinder;
    }

    public function createMetaEntity(string $name, string $bundle = null, string $subDir = null): ?MetaEntity
    {
        return (new MetaEntity($name, $bundle, $subDir))
            ->setUseCustomRepository($this->autoGenerateRepository)
        ;
    }

    public function createMetaEntityForMissingTargetEntity(AbstractRelationshipProperty $property): ?MetaEntity
    {
        if (!$property instanceof AbstractRelationshipProperty || class_exists($property->getTargetEntityFullClassName())) {
            return null;
        }
        $entityNamespace = $property->getTargetEntityFullClassName();
        $entityName = $property->getTargetEntity();
        $bundleName = $this->entityFinder->getBundleNameFromEntityNamespace($entityNamespace);
        $subDir = $this->entityFinder->getSubDirectoryFromEntityNamespace($entityNamespace);

        $targetMetaEntity = new MetaEntity($entityName, $bundleName, $subDir);

        $inversedBy = $property->getInversedBy();
        $mappedBy = $property->getMappedBy();
        if ($newPropertyName = $mappedBy ?: $inversedBy) {
            $inversedType = MetaPropertyFactory::getInversedType($property->getOrmType());
            /** @var AbstractRelationshipProperty $newProperty */
            $newProperty = $this->metaPropertyFactory->getMetaProperty(
                $targetMetaEntity,
                $inversedType,
                $newPropertyName
            );
            if ($inversedBy) {
                $newProperty->setMappedBy($property->getName());
            } else {
                $newProperty->setInversedBy($property->getName());
            }
        }
        return $targetMetaEntity;
    }
}