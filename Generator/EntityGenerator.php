<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityFactory;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Config\FileLocator;

class EntityGenerator
{
    use GeneratorFileLocatorTrait;

    /** @var MetaEntityFactory */
    protected $metaEntityFactory;

    /** @var EntityAppender */
    protected $entityAppender;

    public function __construct(
        MetaEntityFactory $metaEntityFactory,
        EntityAppender $entityAppender,
        FileLocator $fileLocator,
        ?string $overrideSkeletonPath
    ) {
        $this->entityAppender = $entityAppender;
        $this->metaEntityFactory = $metaEntityFactory;
        $this->fileLocator = $fileLocator;
        $this->overrideSkeletonPath = $overrideSkeletonPath;
    }

    public function createEntity(MetaEntity $metaEntity): array
    {
        $entityFileData = $this->getEntityContent($metaEntity);

        $targetFile = $this->getTargetFile($metaEntity);

        $fs = new Filesystem();
        $fs->dumpFile($targetFile, $entityFileData);
        $affectedFiles[] = $targetFile;

        if ($metaEntity->hasCustomRepository()) {
            $affectedFiles[] = $this->createRepository($metaEntity);
        }

        $affectedFiles = array_merge($affectedFiles, $this->generateMissingInversedOrMappedBy($metaEntity));

        return $affectedFiles;
    }

    public function updateEntity(MetaEntity $pseudoMetaEntity): array
    {
        return array_merge(
            [$this->entityAppender->appendFields($pseudoMetaEntity)],
            $this->generateMissingInversedOrMappedBy($pseudoMetaEntity)
        );
    }

    protected function generateMissingInversedOrMappedBy(MetaEntity $metaEntity): array
    {
        $affectedFiles = [];
        foreach ($metaEntity->getRelationshipProperties() as $property) {
            $targetMetaEntity = $property->getTargetEntity();
            $fullClassName = $targetMetaEntity ? $targetMetaEntity->getFullClassName() : null;
            $existingClass = $fullClassName ? class_exists($fullClassName) : false;
            if (!$targetMetaEntity || ($existingClass && $this->checkEntityHasProperty($fullClassName, $property))) {
                continue;
            }
            $this->metaEntityFactory->addMissingProperty($targetMetaEntity, $property);
            if ($existingClass) {
                $affectedFiles[] = $this->entityAppender->appendFields($targetMetaEntity);
            } else {
                $affectedFiles[] = $this->createEntity($targetMetaEntity);
            }
        }
        return $affectedFiles;
    }

    protected function checkEntityHasProperty($fullClassName, AbstractRelationshipProperty $property): bool
    {
        foreach ((new \ReflectionClass($fullClassName))->getProperties() as $reflectionProperty) {
            if (\in_array($reflectionProperty->getName(), [$property->getMappedBy(), $property->getInversedBy()])) {
                return true;
            }
        }
        return false;
    }

    public function createRepository(MetaEntity $metaEntity): string
    {
        $repoFileData = $this->getRepositoryContent($metaEntity);
        $targetFile = str_replace(['/Entity', '.php'], ['/Repository', 'Repository.php'], $this->getTargetFile($metaEntity));

        $fs = new Filesystem();
        $fs->dumpFile($targetFile, $repoFileData);

        return $targetFile;
    }

    protected function getRepositoryContent(MetaEntity $metaEntity)
    {
        return $this->getTwigEnvironment()->render('repository.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
    }

    protected function getEntityContent(MetaEntity $metaEntity)
    {
        return $this->getTwigEnvironment()->render('entity.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
    }
}