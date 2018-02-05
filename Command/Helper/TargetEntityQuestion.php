<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaAttribute;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractRelationshipProperty;
use Symfony\Component\Console\Question\Question;

class TargetEntityQuestion implements AttributeQuestionInterface
{
    use QuestionTrait;

    /** @var EntityFinder */
    protected $entityFinder;

    public function __construct(EntityFinder $entityFinder)
    {
        $this->entityFinder = $entityFinder;
    }

    public function doQuestion(CommandInfo $commandInfo, MetaAttribute $metaAttribute)
    {
        $this->commandInfo = $commandInfo;
        /** @var AbstractRelationshipProperty $metaProperty */
        $metaProperty = $metaAttribute->getMetaProperty();
        if (!$metaProperty || !$this->commandInfo->generatorConfig->askTargetEntity()) {
            return;
        }
        $options = $this->entityFinder->getExistingEntities();
        $this->outputOptions($options);
        $question = new Question('Target entity', $metaProperty->getTargetEntity());
        $question->setAutocompleterValues($options);
        $targetEntity = $this->askQuestion($question);
        $metaProperty->setTargetEntity($targetEntity);
    }
}