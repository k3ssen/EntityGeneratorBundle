<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EntityFinder;
use Kevin3ssen\EntityGeneratorBundle\Generator\EntityGenerator;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;

class MetaEntityFactory
{
    protected $bundles;

    /** @var EntityGenerator */
    protected $entityGenerator;

    /** @var MetaPropertyFactory */
    protected $metaPropertyFactory;

    /** @var bool */
    protected $autoGenerateRepository;

    public function __construct(
        array $bundles,
        ?bool $autoGenerateRepository,
        MetaPropertyFactory $metaPropertyFactory,
        EntityFinder $entityFinder
    )
    {
        $this->bundles = $bundles;
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
        $entityNamespace = $property->getTargetEntityFullClassName();
        if (!$property instanceof AbstractRelationshipProperty || class_exists($entityNamespace)) {
            return null;
        }
        $entityName = $property->getTargetEntity();
        $bundleName = $this->entityFinder->getBundleNameFromEntityNamespace($entityNamespace);
        $subDir = $this->entityFinder->getSubDirectoryFromEntityNamespace($entityNamespace);

        $targetMetaEntity = new MetaEntity($entityName, $bundleName, $subDir);
        $this->addMissingProperty($targetMetaEntity, $property);
        return $targetMetaEntity;
    }

    public function createPseudoMetaEntityForMissingTargetEntityProperty(AbstractRelationshipProperty $property): ?MetaEntity
    {
        $targetEntityFullClassName = $property->getTargetEntityFullClassName();
        if (!$property instanceof AbstractRelationshipProperty || !class_exists($targetEntityFullClassName)) {
            return null;
        }
        $reflector = new \ReflectionClass($targetEntityFullClassName);
        foreach ($reflector->getProperties() as $reflectionProperty) {
            //If the property name is already defined, then nothing needs to be done
            if ($reflectionProperty->getName() === $property->getName()) {
                return null;
            }
        }
        $pseudoMetaEntity = $this->createPseudoMetaEntity($targetEntityFullClassName);
        $this->addMissingProperty($pseudoMetaEntity, $property);
        return $pseudoMetaEntity;
    }

    protected function addMissingProperty(MetaEntity $metaEntity, AbstractRelationshipProperty $property)
    {
        $inversedBy = $property->getInversedBy();
        $mappedBy = $property->getMappedBy();
        if ($newPropertyName = $mappedBy ?: $inversedBy) {
            $inversedType = MetaPropertyFactory::getInversedType($property->getOrmType());
            /** @var AbstractRelationshipProperty $newProperty */
            $newProperty = $this->metaPropertyFactory->getMetaProperty(
                $metaEntity,
                $inversedType,
                $newPropertyName
            );
            if ($inversedBy) {
                $newProperty->setMappedBy($property->getName());
            } else {
                $newProperty->setInversedBy($property->getName());
            }
        }
    }

    public function createPseudoMetaEntity(string $entityFullClassName): MetaEntity
    {
        $reflector = new \ReflectionClass($entityFullClassName);
        $pseudoMetaEntity = new MetaEntity($reflector->getShortName());

        $namespaceParts = explode('\\Entity\\', $entityFullClassName);
        $pseudoMetaEntity->setBundle($this->getBundleName($namespaceParts[0]));

        if (strpos('\\', $namespaceParts[1])) {
            $dirAndNameParts = explode('\\', $namespaceParts[1]);
            $entityName = array_pop($dirAndNameParts);
            if ($entityName !== $pseudoMetaEntity->getName()) {
                throw new \LogicException(sprintf('
                    Tried to retrieve bundle, subdirectory and entityName from "%s", but result is incorrect
                    Expected entity name "%s", but got "%s"
                ', $entityFullClassName, $pseudoMetaEntity->getName(), $entityName));
            }
            $pseudoMetaEntity->setSubDir(implode('/', $dirAndNameParts));
        }
        return $pseudoMetaEntity;
    }

    protected function getBundleName(string $namespaceBeforeEntity): ?string
    {
        foreach ($this->bundles as $bundleName => $bundleNamespace) {
            if ($namespaceBeforeEntity === $bundleNamespace) {
                return $bundleName;
            }
        }
        return null;
    }
}