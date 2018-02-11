<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Config\FileLocator;

class EntityGenerator
{
    use GeneratorTrait;

    public function __construct(FileLocator $fileLocator, ?string $overrideSkeletonPath)
    {
        $this->fileLocator = $fileLocator;
        $this->overrideSkeletonPath = $overrideSkeletonPath;
    }

    public function createEntity(MetaEntity $metaEntity): string
    {
        $entityFileData = $this->getEntityContent($metaEntity);

        $targetFile = $this->getTargetFile($metaEntity);

        $fs = new Filesystem();
        $fs->dumpFile($targetFile, $entityFileData);

        return $targetFile;
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