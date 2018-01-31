<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractPrimitiveProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractRelationshipProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\DecimalProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\HasLengthInterface;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\ManyToManyProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\ManyToOneProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\OneToManyProperty;
use Symfony\Component\Console\Question\Question;

class FieldAttributesQuestionHelper
{
    use QuestionTrait;

    /** @var EntityFinder */
    protected $entityFinder;

    /** @var ValidationQuestionHelper */
    protected $validationQuestionHelper;

    public function __construct(EntityFinder $entityFinder, ValidationQuestionHelper $validationQuestionHelper)
    {
        $this->entityFinder = $entityFinder;
        $this->validationQuestionHelper = $validationQuestionHelper;
    }

    public function setAttributes(CommandInfo $commandInfo, AbstractProperty $metaProperty)
    {
        $this->commandInfo = $commandInfo;
        $this->askId($metaProperty);
        $this->askNullable($metaProperty);
        $this->askUnique($metaProperty);
        $this->askLength($metaProperty);
        $this->askPrecision($metaProperty);
        $this->askScale($metaProperty);
        $this->askTargetEntity($metaProperty);
        $this->askInversedBy($metaProperty);
        $this->askMappedBy($metaProperty);
        $this->validationQuestionHelper->validationAction($commandInfo, $metaProperty);
    }

    protected function askNullable(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askNullable()
            || $metaProperty instanceof OneToManyProperty
            || $metaProperty instanceof ManyToManyProperty
            || $this->commandInfo->metaEntity->getIdProperty() === $metaProperty
        ) {
            return;
        }
        $nullable = $this->confirm('Nullable', $metaProperty->isNullable() ? true : false);
        $metaProperty->setNullable($nullable);
    }

    protected function askUnique(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askUnique()
            || $metaProperty instanceof OneToManyProperty
            || $metaProperty instanceof ManyToManyProperty
            || $this->commandInfo->metaEntity->getIdProperty() === $metaProperty
        ) {
            return;
        }
        $unique = $this->confirm('Unique', $metaProperty->isUnique() ? true : false);
        $metaProperty->setUnique($unique);
    }

    protected function askId(AbstractProperty $metaProperty)
    {
        $currentId = $this->commandInfo->metaEntity->getIdProperty();
        if (!$this->commandInfo->generatorConfig->askId()
            || !$metaProperty instanceof AbstractPrimitiveProperty
            || ($currentId !== null && $currentId !== $metaProperty)
        ) {
            return;
        }
        $id = $this->confirm('Id', $metaProperty->isId() ? true : false);
        $metaProperty->setId($id);
    }

    protected function askLength(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askLength() || !$metaProperty instanceof HasLengthInterface) {
            return;
        }
        $length = $this->ask('Length (optional)', $metaProperty->getLength());
        $metaProperty->setLength($length ? (int) $length : null);
    }

    protected function askPrecision(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askPrecision() || !$metaProperty instanceof DecimalProperty) {
            return;
        }
        $precision = $this->ask('Precision (optional)', $metaProperty->getPrecision());
        $metaProperty->setPrecision($precision ? (int) $precision : null);
    }

    protected function askScale(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askScale() || !$metaProperty instanceof DecimalProperty) {
            return;
        }
        $scale = $this->ask('Scale (optional)', $metaProperty->getScale());
        $metaProperty->setScale($scale ? (int) $scale : null);
    }

    protected function askTargetEntity(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askTargetEntity() || !$metaProperty instanceof AbstractRelationshipProperty) {
            return;
        }
        $options = $this->entityFinder->getExistingEntities();
        $this->outputOptions($options);
        $question = new Question('Target entity', $metaProperty->getTargetEntity());
        $question->setAutocompleterValues($options);
        $targetEntity = $this->askQuestion($question);
        $metaProperty->setTargetEntity($targetEntity);
    }

    protected function askInversedBy(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askInversedBy()
            || !$metaProperty instanceof AbstractRelationshipProperty
            || $metaProperty instanceof OneToManyProperty
        ) {
            return;
        }
        $inversedBy = $this->ask('Inversed by', $metaProperty->getInversedBy());
        $metaProperty->setInversedBy($inversedBy);
    }

    protected function askMappedBy(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askMappedBy()
            || !$metaProperty instanceof AbstractRelationshipProperty
            || $metaProperty->getInversedBy()
            || $metaProperty instanceof ManyToOneProperty
        ) {
            return;
        }
        $mappedBy = $this->ask('Mapped by', $metaProperty->getMappedBy());
        $metaProperty->setMappedBy($mappedBy);
    }
}