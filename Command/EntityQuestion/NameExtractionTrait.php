<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;

trait NameExtractionTrait
{
    protected function extractFromArgument(CommandInfo $commandInfo)
    {
        return $this->extractFromEntityNameAnswer($commandInfo->getInput()->getArgument('entity'));
    }

    /**
     * EntityName could be provided as 'AppBundle:AdminDir/Product'
     * which should resolve as bundle=AppBundle, subDir=AdminDir, entityname=Product
     *
     * @param string|null $entityName
     * @return array in format [bundle, subDir, entityName]
     */
    protected function extractFromEntityNameAnswer(string $entityName = null): array
    {
        if (!$entityName) {
            return [null, null, null];
        }
        $subDir = $bundle = null;
        $entityBundleSplit = explode(':', $entityName);
        if (count($entityBundleSplit) === 2) {
            $bundle = $entityBundleSplit[0];
            $entityName = $entityBundleSplit[1];
        }
        $entityDirSplit = explode(DIRECTORY_SEPARATOR, $entityName);
        if (count($entityDirSplit) > 1) {
            $entityName = array_pop($entityDirSplit);
            $subDir = join(DIRECTORY_SEPARATOR, $entityDirSplit);
        }
        return [$bundle, $subDir, $entityName];
    }
}