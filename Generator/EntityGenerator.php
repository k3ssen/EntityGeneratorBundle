<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Kevin3ssen\EntityGeneratorBundle\Twig\IndentLexer;
use Kevin3ssen\EntityGeneratorBundle\Twig\InflectorExtension;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Config\FileLocator;

class EntityGenerator
{
    /** @var FileLocator */
    protected $fileLocator;
    /** @var GeneratorConfig */
    protected $generatorConfig;

    public function __construct(FileLocator $fileLocator, GeneratorConfig $generatorConfig)
    {
        $this->fileLocator = $fileLocator;
        $this->generatorConfig = $generatorConfig;
    }

    protected function getTargetFile(MetaEntity $metaEntity): string
    {
        $targetBundlePrefix = $metaEntity->getBundle() ? '@'.$metaEntity->getBundle().DIRECTORY_SEPARATOR : '';
        if ($targetBundlePrefix) {
            $bundlePath = $this->fileLocator->locate($targetBundlePrefix);
            if (!file_exists($bundlePath . DIRECTORY_SEPARATOR . 'Entity')) {
                mkdir($bundlePath . DIRECTORY_SEPARATOR . 'Entity');
            }
        }
        $subDirectorySuffix = $metaEntity->getSubDir() ? DIRECTORY_SEPARATOR.$metaEntity->getSubDir() : '';
        $dir = $this->fileLocator->locate($targetBundlePrefix.'Entity').$subDirectorySuffix.DIRECTORY_SEPARATOR;
        return $dir . $metaEntity->getName() . '.php';
    }

    protected function getSkeletonDirs(): array
    {
        $dirs = [];
        if ($skeletonPath = $this->generatorConfig->getOverrideSkeletonPath()) {
            try {
                $dirs[$this->fileLocator->locate($skeletonPath)] = 'App';
            } catch (FileLocatorFileNotFoundException $e) {}
        }
        try {
            $dirs[$this->fileLocator->locate('../templates/EntityGeneratorBundle/skeleton/')] = 'App';
        } catch (FileLocatorFileNotFoundException $e) {}
        try {
            $dirs[$this->fileLocator->locate('../templates/bundles/EntityGeneratorBundle/skeleton/')] = 'App';
        } catch (FileLocatorFileNotFoundException $e) {}
        try {
            $dirs[$this->fileLocator->locate('EntityGenerator/skeleton/')] = 'App';
        } catch (FileLocatorFileNotFoundException $e) {}
        try {
            $dirs[$this->fileLocator->locate('EntityGeneratorBundle/skeleton/')] = 'App';
        } catch (FileLocatorFileNotFoundException $e) {}
        try {
            $dirs[$this->fileLocator->locate('../EntityGenerator/templates/skeleton/')] = 'App';
        } catch (FileLocatorFileNotFoundException $e) {}
        $dirs[$this->fileLocator->locate('@EntityGeneratorBundle/templates/skeleton/')] = 'EntityGeneratorBundle';
        return $dirs;
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

    protected function getTwigEnvironment()
    {
        //Create filesystem with paths in the 'root'
        $twigFileSystem = (new \Twig_Loader_Filesystem(array_keys($this->getSkeletonDirs())));
        //Add paths again, but with namespaces
        foreach ($this->getSkeletonDirs() as $path => $namespace) {
            $twigFileSystem->addPath($path, $namespace);
        }

        $twigEnvironment = new \Twig_Environment($twigFileSystem, [
            'debug' => true,
            'cache' => false,
            'strict_variables' => true,
            'autoescape' => false,
        ]);
        $twigEnvironment->addExtension(new InflectorExtension());
        $twigEnvironment->setLexer(new IndentLexer($twigEnvironment));

        return $twigEnvironment;
    }
}