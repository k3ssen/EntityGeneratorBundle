<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Symfony\Component\HttpKernel\Config\FileLocator;

class EntityAppender
{
    use GeneratorFileLocatorTrait;

    /** @var EntityGenerator */
    protected $entityGenerator;

    public function __construct(
        EntityGenerator $missingTargetEntityGenerator,
        FileLocator $fileLocator,
        ?string $overrideSkeletonPath
    ) {
        $this->entityGenerator = $missingTargetEntityGenerator;
        $this->fileLocator = $fileLocator;
        $this->overrideSkeletonPath = $overrideSkeletonPath;
    }

    public function appendFields(MetaEntity $pseudoMetaEntity): array
    {
        $targetFile = $this->getTargetFile($pseudoMetaEntity);
        $currentContent = file_get_contents($targetFile);

        $this->addUsages($pseudoMetaEntity, $currentContent);
        $this->addConstructorContent($pseudoMetaEntity, $currentContent);
        $this->addProperties($pseudoMetaEntity, $currentContent);
        $this->getAddedMethods($pseudoMetaEntity, $currentContent);
        $addedFiles = $this->entityGenerator->generateMissingTargetEntities($pseudoMetaEntity);

        file_put_contents($targetFile, $currentContent);
        return array_merge([$targetFile], $addedFiles);
    }

    protected function addUsages(MetaEntity $pseudoMetaEntity, string &$currentContent)
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

    protected function addConstructorContent(MetaEntity $pseudoMetaEntity, string &$currentContent)
    {
        $hasConstructor = strpos($currentContent, 'public function __construct(') !== false;
        $propertyContent = $this->getTwigEnvironment()->render('_magic_method_construct.php.twig', [
            'meta_entity' => $pseudoMetaEntity,
            'inner_content_only' => $hasConstructor,
        ]);
        if ($hasConstructor) {
            $this->insertStrAfterLastMatch($currentContent, $propertyContent, '/public function __construct\(.*\)\{/');
        } else {
            $this->insertStrAfterLastMatch($currentContent, $propertyContent, '/(protected|private|public) \$\w+;/');
        }
    }

    protected function addProperties(MetaEntity $pseudoMetaEntity, string &$currentContent)
    {
        $propertyContent = $this->getTwigEnvironment()->render('properties.php.twig', [
            'meta_entity' => $pseudoMetaEntity,
            'skip_id' => true,
        ]);
        $this->insertStrAfterLastMatch($currentContent, $propertyContent, '/(protected|private|public) \$\w+;/');
    }

    protected function getAddedMethods(MetaEntity $pseudoMetaEntity, string &$currentContent)
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
        $position = $lastMatch[1] + strlen($lastMatch[0]) + 1;
        $baseString = substr_replace($baseString, $insertString, $position, 0);
    }
}
