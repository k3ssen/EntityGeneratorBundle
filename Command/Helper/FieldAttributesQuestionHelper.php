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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

class FieldAttributesQuestionHelper extends QuestionHelper
{
    use QuestionTrait;

    /** @var EntityFinder */
    protected $entityFinder;

    public function __construct(EntityFinder $entityFinder)
    {
        $this->entityFinder = $entityFinder;
    }

    public function setAttributes(CommandInfo $commandInfo, AbstractProperty $metaProperty)
    {
        $this->commandInfo = $commandInfo;
        $this->askNullable($metaProperty);
        $this->askUnique($metaProperty);
        $this->askId($metaProperty);
        $this->askLength($metaProperty);
        $this->askPrecision($metaProperty);
        $this->askScale($metaProperty);
        $this->askTargetEntity($metaProperty);
        $this->askInversedBy($metaProperty);
        $this->askMappedBy($metaProperty);
    }

    protected function askNullable(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askNullable()
            || $metaProperty instanceof OneToManyProperty
            || $metaProperty instanceof ManyToManyProperty
        ) {
            return;
        }
        $nullable = $this->askSimpleQuestion('Nullable', $metaProperty->isNullable() ? true : false);
        $metaProperty->setNullable($nullable);
    }

    protected function askUnique(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askUnique()
            || $metaProperty instanceof OneToManyProperty
            || $metaProperty instanceof ManyToManyProperty
        ) {
            return;
        }
        $unique = $this->askSimpleQuestion('Unique', $metaProperty->isUnique() ? true : false);
        $metaProperty->setUnique($unique);
    }

    protected function askId(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askId() || !$metaProperty instanceof AbstractPrimitiveProperty) {
            return;
        }
        $id = $this->askSimpleQuestion('Id', $metaProperty->isId() ? true : false);
        $metaProperty->setId($id);
    }

    protected function askLength(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askLength() || !$metaProperty instanceof HasLengthInterface) {
            return;
        }
        $length = $this->askSimpleQuestion('Length', $metaProperty->getLength());
        $metaProperty->setLength($length ? (int) $length : null);
    }

    protected function askPrecision(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askPrecision() || !$metaProperty instanceof DecimalProperty) {
            return;
        }
        $precision = $this->askSimpleQuestion('Precision', $metaProperty->getPrecision());
        $metaProperty->setPrecision($precision ? (int) $precision : null);
    }

    protected function askScale(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askScale() || !$metaProperty instanceof DecimalProperty) {
            return;
        }
        $scale = $this->askSimpleQuestion('Scale', $metaProperty->getScale());
        $metaProperty->setScale($scale ? (int) $scale : null);
    }

    protected function askTargetEntity(AbstractProperty $metaProperty)
    {
        if (!$this->commandInfo->generatorConfig->askTargetEntity() || !$metaProperty instanceof AbstractRelationshipProperty) {
            return;
        }
        $options = $this->entityFinder->getExistingEntities();
        $this->outputOptions($options);
        $defaultText = $metaProperty->getTargetEntity() ? ' [<comment>'.$metaProperty->getTargetEntity().'</comment>]' : '';
        $question = new Question('<info>Target entity</info>'.$defaultText.': ', $metaProperty->getTargetEntity());
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
        $inversedBy = $this->askSimpleQuestion('Inversed by', $metaProperty->getInversedBy());
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
        $mappedBy = $this->askSimpleQuestion('Mapped by', $metaProperty->getMappedBy());
        $metaProperty->setMappedBy($mappedBy);
    }
}