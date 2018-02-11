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
        $createdFiles[] = $targetFile;

        if ($metaEntity->hasCustomRepository()) {
            $createdFiles[] = $this->createRepository($metaEntity);
        }

        $createdFiles = array_merge($createdFiles, $this->generateMissingTargetEntities($metaEntity));

        return $createdFiles;
    }

    public function generateMissingTargetEntities(MetaEntity $metaEntity): array
    {
        $addedFiles = [];
        foreach ($metaEntity->getProperties() as $property) {
            $targetMetaEntity = $this->metaEntityFactory->createMetaEntityForMissingTargetEntity($property);
            if (!$targetMetaEntity) {
                continue;
            }
            $addedFiles[] = $this->createEntity($targetMetaEntity);
            if ($targetMetaEntity->hasCustomRepository()) {
                $addedFiles[] = $this->createRepository($targetMetaEntity);
            }
        }
        return $addedFiles;
    }

    public function appendMissingInversionsToTargetEntities(MetaEntity $metaEntity): array
    {
        $alteredFiles = [];
        foreach ($metaEntity->getProperties() as $property) {
            $pseudoMetaEntity = $this->metaEntityFactory->createPseudoMetaEntityForMissingTargetEntityProperty($property);
            if (!$pseudoMetaEntity) {
                continue;
            }
            $alteredFiles[] =$this->entityAppender->appendFields($pseudoMetaEntity);
        }
        return $alteredFiles;
    }

    public function createRepository(MetaEntity $metaEntity): string
    {
        $repoFileData = $this->getRepositoryContent($metaEntity);
        $targetFile = str_replace(['/Entity', '.php'], ['/Repository', 'Repository.php'], $this->getTargetFile($metaEntity));

        $fs = new Filesystem();
        $fs->dumpFile($targetFile, $repoFileData);

        return $targetFile;
    }

    public function getRepositoryContent(MetaEntity $metaEntity)
    {
        return $this->getTwigEnvironment()->render('repository.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
    }

    public function getEntityContent(MetaEntity $metaEntity)
    {
        return $this->getTwigEnvironment()->render('entity.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
    }
}