<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\Generator;

use K3ssen\EntityGeneratorBundle\MetaData\MetaEntityInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;

class EntityAppender
{
    use GeneratorFileLocatorTrait;

    public function __construct(
        FileLocator $fileLocator,
        ?string $overrideSkeletonPath
    ) {
        $this->fileLocator = $fileLocator;
        $this->overrideSkeletonPath = $overrideSkeletonPath;
    }

    public function appendFields(MetaEntityInterface $pseudoMetaEntity): string
    {
        $targetFile = $this->getTargetFile($pseudoMetaEntity);
        $currentContent = file_get_contents($targetFile);

        $this->addUsages($pseudoMetaEntity, $currentContent);
        $this->addConstructorContent($pseudoMetaEntity, $currentContent);
        $this->addProperties($pseudoMetaEntity, $currentContent);
        $this->getAddedMethods($pseudoMetaEntity, $currentContent);

        file_put_contents($targetFile, $currentContent);
        return $targetFile;
    }

    protected function addUsages(MetaEntityInterface $pseudoMetaEntity, string &$currentContent)
    {
        //First we check and remove usages that are already defined.
        foreach ($pseudoMetaEntity->getUsages() as $usageNamespace => $usageAlias) {
            if (strpos($currentContent, $usageNamespace) !== false) {
                $pseudoMetaEntity->removeUsage($usageNamespace);
            }
        }
        $usageContent = $this->getTwigEnvironment()->render('_usages.php.twig', [
            'meta_entity' => $pseudoMetaEntity,
        ]);

        $this->insertStrAfterLastMatch($currentContent, $usageContent, '/use .*;/');
    }

    protected function addConstructorContent(MetaEntityInterface $pseudoMetaEntity, string &$currentContent)
    {
        $hasConstructor = strpos($currentContent, 'public function __construct(') !== false;
        $propertyContent = $this->getTwigEnvironment()->render('_magic_method_construct.php.twig', [
            'meta_entity' => $pseudoMetaEntity,
            'inner_content_only' => $hasConstructor,
        ]);
        if ($hasConstructor) {
            $this->insertStrAfterLastMatch($currentContent, $propertyContent, '/public function __construct\(.*\)\n    /');
        } else {
            $this->insertStrAfterLastMatch($currentContent, $propertyContent, '/(protected|private|public) \$\w+;/');
        }
    }

    protected function addProperties(MetaEntityInterface $pseudoMetaEntity, string &$currentContent)
    {
        $propertyContent = $this->getTwigEnvironment()->render('properties.php.twig', [
            'meta_entity' => $pseudoMetaEntity,
            'skip_id' => true,
        ]);
        $this->insertStrAfterLastMatch($currentContent, $propertyContent, '/(protected|private|public) \$\w+;/');
    }

    protected function getAddedMethods(MetaEntityInterface $pseudoMetaEntity, string &$currentContent)
    {
        $methodsContent = $this->getTwigEnvironment()->render('property_methods.php.twig', [
            'meta_entity' => $pseudoMetaEntity,
            'skip_id' => true,
        ]);

        preg_match_all('/\}/', $currentContent, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = array_pop($matches[0]);
        $position = $lastMatch[1];
        $currentContent = substr_replace($currentContent, $methodsContent, $position, 0);
    }

    protected function insertStrAfterLastMatch(string &$baseString, string $insertString, string $pattern)
    {
        preg_match_all($pattern, $baseString, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = array_pop($matches[0]);
        if (is_array($lastMatch) && count($lastMatch) > 1) {
            $position = $lastMatch[1] + strlen($lastMatch[0]) + 1;
            $baseString = substr_replace($baseString, $insertString, $position, 0);
        }
    }
}
