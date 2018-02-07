<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntityFactory;

class EntityNameQuestion implements EntityQuestionInterface
{
    use NameExtractionTrait;

    /** @var MetaEntityFactory */
    protected $metaEntityFactory;

    public function __construct(MetaEntityFactory $metaEntityFactory) {
        $this->metaEntityFactory = $metaEntityFactory;
    }

    public function addActions(CommandInfo $commandInfo, array &$actions) {
        $actions['Edit entity name'] = function() use($commandInfo) { $this->doQuestion($commandInfo); };
    }

    public function doQuestion(CommandInfo $commandInfo)
    {
        $commandInfo->getIo()->title('Create new entity');

        try {
            $metaEntity = $commandInfo->getMetaEntity();
        } catch (\RuntimeException $exception) {
            [$bundle, $subDir, $entityName] = $this->extractFromArgument($commandInfo);
            $metaEntity = $entityName ? $this->metaEntityFactory->createMetaEntity($entityName, $bundle, $subDir) : null;
        }
        $nameAnswer = $commandInfo->getIo()->ask('Entity name', $entityName, function ($value) {
            if (!$value) {
                throw new \InvalidArgumentException('The entity name cannot be empty');
            }
            return $value;
        });
        [$bundle, $subDir, $entityName] = $this->extractFromEntityNameAnswer($nameAnswer);
        if (!$metaEntity) {
            $metaEntity = $this->metaEntityFactory->createMetaEntity($entityName, $bundle, $subDir);
        } else {
            $metaEntity->setName($entityName);
            if ($bundle) {
                $metaEntity->setBundle($bundle);
            }
            if ($subDir) {
                $metaEntity->setSubDir($subDir);
            }
        }
        $commandInfo->setMetaEntity($metaEntity);
    }
}