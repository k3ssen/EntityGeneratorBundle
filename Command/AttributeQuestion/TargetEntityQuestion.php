<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EntityFinder;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttribute;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;
use Symfony\Component\Console\Question\Question;

class TargetEntityQuestion implements AttributeQuestionInterface
{
    /** @var EntityFinder */
    protected $entityFinder;

    protected $attributeName;

    public function __construct(array $attributes, string $attributeName, EntityFinder $entityFinder)
    {
        if (!array_key_exists($attributeName, $attributes)) {
            throw new \InvalidArgumentException(sprintf('attribute name "%s" has not been defined in the "attributes" configuration', $attributeName));
        }
        $this->attributeName = $attributeName;
        $this->entityFinder = $entityFinder;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function doQuestion(CommandInfo $commandInfo, MetaAttribute $metaAttribute)
    {
        /** @var AbstractRelationshipProperty $metaProperty */
        $metaProperty = $metaAttribute->getMetaProperty();
        if (!$metaProperty) {
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