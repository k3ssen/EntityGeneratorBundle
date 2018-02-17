<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityFactory;

class EntityNameQuestion implements EntityQuestionInterface
{
    /** @var MetaEntityFactory */
    protected $metaEntityFactory;

    public function __construct(MetaEntityFactory $metaEntityFactory)
    {
        $this->metaEntityFactory = $metaEntityFactory;
    }

    public function addActions(CommandInfo $commandInfo, array &$actions) {
        $actions['Edit entity name'] = function() use($commandInfo) { $this->doQuestion($commandInfo); };
    }

    public function doQuestion(CommandInfo $commandInfo)
    {
        try {
            $metaEntity = $commandInfo->getMetaEntity();
            $entityName = $metaEntity->getName();
        } catch (\RuntimeException $exception) {
            $entityName = $commandInfo->getInput()->getArgument('entity');
        }
        $nameAnswer = $commandInfo->getIo()->ask('Entity name', $entityName, function ($value) {
            if (!$value) {
                throw new \InvalidArgumentException('The entity name cannot be empty');
            }
            return $value;
        });
        if (isset($metaEntity)) {
            $metaEntity->setName($nameAnswer);
            return;
        }
        try {
            $metaEntity = $this->metaEntityFactory->createByShortcutNotation($nameAnswer);
            $commandInfo->setMetaEntity($metaEntity);
        } catch (\InvalidArgumentException $exception) {
            $commandInfo->getIo()->error($exception->getMessage());
            $this->doQuestion($commandInfo);
        }
    }
}