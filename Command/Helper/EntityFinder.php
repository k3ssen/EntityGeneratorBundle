<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;

class EntityFinder
{
    use QuestionTrait;
    /** @var ClassMetadataFactory */
    protected $classMetadataFactory;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->classMetadataFactory = $entityManager->getMetadataFactory();
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
}