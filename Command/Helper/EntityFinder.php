<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;

class EntityFinder
{
    /** @var ClassMetadataFactory */
    protected $classMetadataFactory;

    protected $bundles;

    public function __construct(EntityManagerInterface $entityManager, array $bundles)
    {
        $this->classMetadataFactory = $entityManager->getMetadataFactory();
        $this->bundles = $bundles;
    }

    public function findEntityClass(string $name, bool $includeNamespace = false): ?string
    {
        $columnName = Inflector::tableize($name);
        foreach ($this->getExistingEntities() as $nameSpace => $entityName) {
            if ($columnName === Inflector::tableize($entityName)) {
                return $includeNamespace ? $nameSpace : $entityName;
            }
        }
        return null;
    }

    public function getExistingEntities(): array
    {
        if (isset($this->existingEntities)) {
            return $this->existingEntities;
        }
        /** @var ClassMetadata[] $entityMetadata */
        $entityMetadata = $this->classMetadataFactory->getAllMetadata();

        $entities = [];
        foreach ($entityMetadata as $meta) {
            $entities[$meta->getName()] = $meta->reflClass->getShortName();;
        }
        return $this->existingEntities = $entities;
    }

    public function getEntityNameFromEntityNamespace(string $entityNamespace): string
    {
        $parts = explode('\\', $entityNamespace);
        return array_pop($parts);
    }

    public function getBundleNameFromEntityNamespace(string $entityNamespace): ?string
    {
        $entityBundleNamespace = $this->getBundleNamespaceFromEntityNamespace($entityNamespace);
        foreach ($this->bundles as $bundleName => $bundleNamespace) {
            if ($entityBundleNamespace === $bundleNamespace) {
                return $bundleName;
            }
        }
        return null;
    }

    public function getBundleNamespaceFromEntityNamespace(string $entityNamespace): ?string
    {
        $parts = explode('\\Entity\\', $entityNamespace);
        return $parts[0];
    }

    public function getSubDirectoryFromEntityNamespace(string $entityNamespace): ?string
    {
        $parts = explode('\\Entity\\', $entityNamespace);
        $subDirAndEntityName = $parts[1];
        if (strpos('\\', $subDirAndEntityName) !== false) {
            $subDirAndEntityNameParts = explode('\\', $subDirAndEntityName);
            array_pop($subDirAndEntityNameParts);
            return implode(DIRECTORY_SEPARATOR, $subDirAndEntityNameParts);
        }
        return null;
    }
}