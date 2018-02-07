<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\PropertyQuestion\PropertyQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntityFactory;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;

class FieldsQuestion implements EntityQuestionInterface
{
    use NameExtractionTrait;

    /** @var MetaEntityFactory */
    protected $metaEntityFactory;

    /** @var iterable|PropertyQuestionInterface[] */
    protected $propertyQuestions;

    public function __construct(MetaEntityFactory $metaEntityFactory, iterable $propertyQuestions) {
        $this->metaEntityFactory = $metaEntityFactory;
        $this->propertyQuestions = $propertyQuestions;
    }

    public function addActions(CommandInfo $commandInfo, array &$actions) {
        $actions['New field'] = function() use($commandInfo) { $this->addNewField($commandInfo); };
        $actions['Edit field'] = function() use($commandInfo) { $this->editField($commandInfo); };
        $actions['Remove field'] = function() use($commandInfo) { $this->removeField($commandInfo); };
    }

    public function doQuestion(CommandInfo $commandInfo)
    {
        $this->addNewField($commandInfo);
    }

    public function addNewField(CommandInfo $commandInfo)
    {
        $commandInfo->getIo()->section('Add new field');
        foreach ($this->propertyQuestions as $propertyQuestion) {
            $metaProperty = $commandInfo->getMetaEntity()->getProperties()->last() ?: null;
            $propertyQuestion->doQuestion($commandInfo, $metaProperty);
        }
    }

    public function editField(CommandInfo $commandInfo)
    {
        $commandInfo->getIo()->section('Edit field');
        $metaProperty = $this->chooseField($commandInfo);
        foreach ($this->propertyQuestions as $propertyQuestion) {
            $propertyQuestion->doQuestion($commandInfo, $metaProperty);
        }

//        $this->validationQuestionHelper->validationAction($commandInfo, $metaProperty);
    }

    public function removeField(CommandInfo $commandInfo)
    {
        $commandInfo->getIo()->section('Remove field');
        $metaProperty = $this->chooseField($commandInfo);
        if ($commandInfo->getMetaEntity()->getDisplayProperty() === $metaProperty) {
            $commandInfo->getMetaEntity()->setDisplayProperty(null);
        }
        $commandInfo->getMetaEntity()->removeProperty($metaProperty);
        unset($metaProperty);
    }

    protected function chooseField(CommandInfo $commandInfo): AbstractProperty
    {
        $properties = $commandInfo->getMetaEntity()->getProperties();
        $propertyChoice = $commandInfo->getIo()->choice('choice', $properties->toArray());
        foreach ($properties as $property) {
            if ($property->getName() === $propertyChoice) {
                return $property;
            }
        }
        throw new \RuntimeException(sprintf('No property found for choice %s', $propertyChoice));
    }
}