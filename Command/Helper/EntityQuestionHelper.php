<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntityFactory;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;

class EntityQuestionHelper
{
    use QuestionTrait;

    protected const ACTION_NEW_PROPERTY = 1;
    protected const ACTION_EDIT_PROPERTY = 2;
    protected const ACTION_DELETE_PROPERTY = 3;
    protected const ACTION_CHANGE_ENTITY_NAME = 4;
    protected const ACTION_SET_DISPLAY_FIELD = 5;
    protected const ACTION_COMPLETE = 6;

    /** @var MetaEntityFactory */
    protected $metaEntityFactory;
    /** @var FieldQuestionHelper */
    protected $fieldTypeQuestionHelper;

    public function __construct(
        MetaEntityFactory $metaEntityFactory,
        FieldQuestionHelper $fieldTypeQuestionHelper
    ) {
        $this->metaEntityFactory = $metaEntityFactory;
        $this->fieldTypeQuestionHelper = $fieldTypeQuestionHelper;
    }

    public function makeEntity(CommandInfo $commandInfo, string $entityName = null): ?MetaEntity
    {
        $this->commandInfo = $commandInfo;
        [$bundle, $subDir, $entityName] = $this->extractFromEntityNameAnswer($entityName);

        $this->header('Create new entity');

        $this->askNameAndCreateEntity($entityName);
        $this->askBundle($bundle);
        $this->askSubDir($subDir);
        //TODO: trait-questions
        do {
            $metaProperty = $this->fieldTypeQuestionHelper->makeField($this->commandInfo);
        } while ($metaProperty !== null);

        $this->askDisplayField();
        $this->saveTemporaryFile();
        $this->askAction();
        return $commandInfo->metaEntity;
    }

    public function continueEntity(CommandInfo $commandInfo, MetaEntity $metaEntity)
    {
        $commandInfo->metaEntity = $metaEntity;
        $this->commandInfo = $commandInfo;
        $this->header(sprintf('Continue creating entity "%s"', $metaEntity->getName()));
        $this->askAction();
        return $commandInfo->metaEntity;
    }

    protected function askAction()
    {
        $this->showCurrentOverview();
        $actionChoices = [
            static::ACTION_NEW_PROPERTY => 'Add field',
            static::ACTION_EDIT_PROPERTY => 'Edit field',
            static::ACTION_DELETE_PROPERTY => 'Remove field',
            static::ACTION_CHANGE_ENTITY_NAME => 'Change entity name / bundle / directory',
            static::ACTION_SET_DISPLAY_FIELD => 'Set display field',
            static::ACTION_COMPLETE => 'All done! Generate entity!',
            //Savepoint with name
        ];
        if (!$this->commandInfo->metaEntity->getProperties()->count()) {
            unset($actionChoices[static::ACTION_EDIT_PROPERTY], $actionChoices[static::ACTION_DELETE_PROPERTY], $actionChoices[static::ACTION_SET_DISPLAY_FIELD]);
        }
        $nextAction = $this->getIo()->choice('What to do next?' , $actionChoices);
        $nextAction = array_search($nextAction, $actionChoices) ?: $nextAction;

        switch ($nextAction) {
            case static::ACTION_COMPLETE;
                $this->saveTemporaryFile();
                return;
            case static::ACTION_CHANGE_ENTITY_NAME:
                $this->askNewEntityName();
                $this->askBundle();
                $this->askSubDir();
                break;
            case static::ACTION_SET_DISPLAY_FIELD:
                $this->askDisplayField();
                $this->saveTemporaryFile();
                break;
            case static::ACTION_DELETE_PROPERTY:
                $metaProperty = $this->askPropertyChoice();
                if ($this->commandInfo->metaEntity->getDisplayProperty() === $metaProperty) {
                    $this->commandInfo->metaEntity->setDisplayProperty(null);
                }
                $this->commandInfo->metaEntity->removeProperty($metaProperty);
                unset($metaProperty);
                break;
            case static::ACTION_EDIT_PROPERTY:
                $metaProperty = $this->askPropertyChoice();
                $this->fieldTypeQuestionHelper->makeField($this->commandInfo, $metaProperty);
                break;
            case static::ACTION_NEW_PROPERTY:
                do {
                    $metaProperty = $this->fieldTypeQuestionHelper->makeField($this->commandInfo);
                } while ($metaProperty !== null);
                $this->askDisplayField();
                break;
        }
        $this->saveTemporaryFile();
        $this->askAction();
    }

    protected function askTraits()
    {

    }

    protected function askPropertyChoice(): AbstractProperty
    {
        $properties = $this->commandInfo->metaEntity->getProperties();
        $propertyChoice = $this->getIo()->choice('Edit property', $properties->toArray());
        foreach ($properties as $property) {
            if ($property->getName() === $propertyChoice) {
                return $property;
            }
        }
        throw new \RuntimeException(sprintf('No property found for choice %s', $propertyChoice));
    }

    protected function askNameAndCreateEntity(string $entityName = null)
    {
        $entityName = $this->ask('Entity name', $entityName, function ($value) {
            if (!$value) {
                throw new \InvalidArgumentException('The entity name cannot be empty');
            }
            return $value;
        });
        [$bundle, $subDir, $entityName] = $this->extractFromEntityNameAnswer($entityName);
        $this->commandInfo->metaEntity = $this->metaEntityFactory->createMetaEntity($entityName, $bundle, $subDir);
    }

    protected function askNewEntityName()
    {
        $entityName = $this->ask('Entity name', $this->commandInfo->metaEntity->getName(), function ($value) {
            if (!$value) {
                throw new \InvalidArgumentException('The entity name cannot be empty');
            }
            return $value;
        });
        $this->commandInfo->metaEntity->setName($entityName);
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

    protected function askBundle(string $bundle = null)
    {
        if (!$this->commandInfo->generatorConfig->askBundle()) {
            return;
        }
        $bundle = $this->ask('Bundle (optional)', $this->commandInfo->metaEntity->getBundle() ?: $bundle);
        $this->commandInfo->metaEntity->setBundle($bundle);
    }

    protected function askSubDir(string $subDir = null)
    {
        if (!$this->commandInfo->generatorConfig->askSubDir()) {
            return;
        }
        $subDir = $this->ask('Sub directory (optional)', $this->commandInfo->metaEntity->getSubDir() ?: $subDir);
        $this->commandInfo->metaEntity->setSubDir($subDir);
    }

    protected function askDisplayField()
    {
        if (!$this->commandInfo->generatorConfig->askDisplayField()) {
            return;
        }
        $propertyOptions = ['' => null];
        foreach ($this->commandInfo->metaEntity->getProperties() as $property) {
            if (in_array($property->getReturnType(), ['string', 'int'], true)) {
                $propertyOptions[$property->getName()] = $property;
            }
        }
        if (count($propertyOptions) === 1) {
            $this->getIo()->warning('There are no properties suitable for using as display field automatically.');
            return;
        }
        $defaultDisplayField = $this->commandInfo->metaEntity->getDisplayProperty();
        $answer = $this->getIo()->choice('Display field (optional)', array_keys($propertyOptions), $defaultDisplayField ? (string) $defaultDisplayField : null);
        $property = $propertyOptions[$answer] ?? $answer;
        $this->commandInfo->metaEntity->setDisplayProperty($property === '' ? null : $property);
    }
}