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
    /** @var \Twig_Environment */
    protected $twigEngine;

    /** @var FileLocator */
    protected $fileLocator;

    public function __construct(\Twig_Environment $twigEngine, FileLocator $fileLocator)
    {
        $this->twigEngine = $twigEngine;
        $this->fileLocator = $fileLocator;
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
        try {
            $dirs[$this->fileLocator->locate('../templates/EntityGeneratorBundle/')] = 'App';
        } catch (FileLocatorFileNotFoundException $e) {}
        try {
            $dirs[$this->fileLocator->locate('../templates/bundles/EntityGeneratorBundle/')] = 'App';
        } catch (FileLocatorFileNotFoundException $e) {}
        $dirs[$this->fileLocator->locate('@EntityGeneratorBundle/templates/')] = 'EntityGeneratorBundle';
        return $dirs;
    }

    public function createEntity($dataInput = null): string
    {
        $metaEntity = static::createExampleMetaEntity();
        $entityFileData = $this->getEntityContent($metaEntity);

        $targetFile = $this->getTargetFile($metaEntity);

        $fs = new Filesystem();
        $fs->dumpFile($targetFile, $entityFileData);
        return $targetFile;
    }

    public function getEntityContent(MetaEntity $metaEntity)
    {
        return $this->getTwigEnvironment()->render('skeleton/entity.php.twig', [
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

    public static function createExampleMetaEntity()
    {
        $metaEntity = new MetaEntity('Library', 'EntityGeneratorBundle', 'Admin');

        $title = (new MetaData\Property\StringProperty($metaEntity, 'title'));

        $metaEntity->setDisplayProperty($title);

        (new MetaData\Property\IntegerProperty($metaEntity, 'numberOfSomething'))
            ->setNullable(true)
            ->setLength(6);

        (new MetaData\Property\ManyToOneProperty($metaEntity, 'country'))
            ->setNullable(true)
        ;

        (new MetaData\Property\OneToManyProperty($metaEntity, 'books'))
            ->setTargetEntityNamespace('SomeOtherBundle\\Entity')
            ->setNullable(true)
        ;

        return $metaEntity;
    }
}