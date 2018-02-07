<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\AttributeQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EntityFinder;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaAttribute;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractRelationshipProperty;
use Symfony\Component\Console\Question\Question;

class TargetEntityQuestion implements AttributeQuestionInterface
{
    /** @var EntityFinder */
    protected $entityFinder;

    public function __construct(EntityFinder $entityFinder)
    {
        $this->entityFinder = $entityFinder;
    }

    public function doQuestion(CommandInfo $commandInfo, MetaAttribute $metaAttribute)
    {
        /** @var AbstractRelationshipProperty $metaProperty */
        $metaProperty = $metaAttribute->getMetaProperty();
        if (!$metaProperty || !$commandInfo->getGeneratorConfig()->askTargetEntity()) {
            return;
        }
        $options = $this->entityFinder->getExistingEntities();
        $commandInfo->getIo()->listing($options);
        $question = new Question('Target entity', $metaProperty->getTargetEntity());
        $question->setAutocompleterValues($options);
        $targetEntity = $commandInfo->getIo()->askQuestion($question);
        $metaProperty->setTargetEntity($targetEntity);
    }
}