<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\Generator;

use K3ssen\MetaEntityBundle\MetaData\MetaEntityInterface;
use K3ssen\EntityGeneratorBundle\Twig\IndentLexer;
use K3ssen\EntityGeneratorBundle\Twig\InflectorExtension;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\HttpKernel\Config\FileLocator;

trait GeneratorFileLocatorTrait
{
    /** @var FileLocator */
    protected $fileLocator;

    protected $overrideSkeletonPath;

    protected function getTargetFile(MetaEntityInterface $metaEntity): string
    {
        $targetBundlePrefix = $metaEntity->getBundleNamespace() ? '@'.$metaEntity->getBundleNamespace().DIRECTORY_SEPARATOR : '';
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
        if ($skeletonPath = $this->overrideSkeletonPath) {
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
        $dirs[$this->fileLocator->locate('@EntityGeneratorBundle/Resources/views/skeleton/')] = 'EntityGeneratorBundle';
        return $dirs;
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