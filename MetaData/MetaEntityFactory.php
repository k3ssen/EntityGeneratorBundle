<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Doctrine\ORM\EntityManagerInterface;
use Kevin3ssen\EntityGeneratorBundle\Generator\EntityGenerator;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;

class MetaEntityFactory
{
    /** @var BundleProvider */
    protected $bundleProvider;

    /** @var EntityGenerator */
    protected $entityGenerator;

    /** @var MetaPropertyFactory */
    protected $metaPropertyFactory;

    /** @var bool */
    protected $autoGenerateRepository;

    /** @var ClassMetadataFactory */
    protected $classMetadataFactory;

    public function __construct(
        BundleProvider $bundleProvider,
        ?bool $autoGenerateRepository,
        MetaPropertyFactory $metaPropertyFactory,
        EntityManagerInterface $em
    )
    {
        $this->bundleProvider = $bundleProvider;
        $this->autoGenerateRepository = $autoGenerateRepository;
        $this->metaPropertyFactory = $metaPropertyFactory;
        $this->classMetadataFactory = $em->getMetadataFactory();
    }

    /**
     * Creates a MetaEntity by the provided ClassName.
     * Preferably the fullClassName is provided, so that bundle and subdirectory can be automatically subtracted.
     * If only a name (without namespace) is provided, defaults will be used.
     */
    public function createByClassName(string $nameOrFullClassName): MetaEntity
    {
        return (new MetaEntity($nameOrFullClassName))
            ->setUseCustomRepository($this->autoGenerateRepository)
        ;
    }

    /**
     * Creates a MetaEntity by shortcutNotation rather than (full)ClassName.
     * Unlike with the fullClassName, you'd only need to provide the bundleName rather than its namespace.
     *
     * Possible notations are:
     *  - BundleName:Subdirectory/MetaEntityName    to specify bundle, subdir and entityName
     *  - BundleName:MetaEntityName                 to specify bundle and entityName, but no subdir
     *  - SubDirectory/EntityName                   to specify subDir and entityName, but no bundle
     *  - EntityName                                to specify entityName, but no subDir and no bundle
     */
    public function createByShortcutNotation(string $shortcutNotation): MetaEntity
    {
        $entityName = $shortcutNotation;
        $bundleName = $subDir = null;
        if (strpos($shortcutNotation, ':') !== false) {
            $parts = explode(':', $shortcutNotation);
            $bundleName = array_shift($parts);
            $entityName =  implode('/', $parts);
        }
        if (strpos($shortcutNotation, '/') !== false) {
            $parts = explode('/', $shortcutNotation);
            $entityName = array_pop($parts);
            $subDir = implode('/', $parts);
        }
        $bundleNamespace = $this->bundleProvider->getBundleNamespaceByName($bundleName);
        $fullClassName = $bundleNamespace . '\\Entity\\' . ($subDir ? $subDir.'\\' : '') . $entityName;
        return $this->createByClassName($fullClassName);
    }

    /**
     * Retrieves list of existing entities as MetaEntities (only fullClassName is set on these MetaEntities)
     * @return array|MetaEntity[]
     */
    public function getEntityOptions(): array
    {
        if (isset($this->existingEntities)) {
            return $this->existingEntities;
        }
        /** @var ClassMetadata[] $entityMetadata */
        $entityMetadata = $this->classMetadataFactory->getAllMetadata();

        $entities = [];
        foreach ($entityMetadata as $meta) {
            $entities[] = new MetaEntity($meta->getName());
        }
        return $this->existingEntities = $entities;
    }

    public function getMetaEntityByChosenOption($choice): ?MetaEntity
    {
        $options = $this->getEntityOptions();
        foreach ($options as $key => $metaEntity) {
            if ((string) $metaEntity === $choice) {
                return $metaEntity;
            }
        }
        return null;
    }

    public function getDoctrineOrmClassMetadata(string $entityFullClassName): ClassMetadata
    {
        $classMetaData = $this->classMetadataFactory->getMetadataFor($entityFullClassName);
        return $classMetaData instanceof ClassMetadata ? $classMetaData : null;
    }

    /**
     * Adds missing field for inversedBy or mappedBy
     *
     * @param MetaEntity $metaEntity
     * @param AbstractRelationshipProperty $property
     */
    public function addMissingProperty(MetaEntity $metaEntity, AbstractRelationshipProperty $property)
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
            $newProperty->setTargetEntity($property->getMetaEntity());
            if ($inversedBy) {
                $newProperty->setMappedBy($property->getName());
            } else {
                $newProperty->setInversedBy($property->getName());
            }
        }
    }
}