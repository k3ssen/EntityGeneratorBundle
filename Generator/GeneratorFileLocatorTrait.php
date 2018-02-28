<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\Generator;

use K3ssen\MetaEntityBundle\MetaData\MetaEntityInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;

trait GeneratorFileLocatorTrait
{
    /** @var FileLocator */
    protected $fileLocator;

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
}