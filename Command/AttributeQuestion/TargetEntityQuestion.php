<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttributeInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityFactory;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\RelationMetaPropertyInterface;
use Symfony\Component\Console\Question\Question;

class TargetEntityQuestion implements AttributeQuestionInterface
{
    /** @var MetaEntityFactory */
    protected $metaEntityFactory;

    /** @var array */
    protected $supportedAttributes;

    public function __construct(MetaEntityFactory $metaEntityFactory)
    {
        $this->metaEntityFactory = $metaEntityFactory;
    }

    public function addAttribute(string $attributeName, array $attributeInfo = [])
    {
        $this->supportedAttributes[$attributeName] = $attributeInfo;
    }

    public function supportsAttribute(string $attributeName): bool
    {
        return array_key_exists($attributeName, $this->supportedAttributes);
    }

    public function doQuestion(CommandInfo $commandInfo, MetaAttributeInterface $metaAttribute)
    {
        /** @var RelationMetaPropertyInterface $metaProperty */
        $metaProperty = $metaAttribute->getMetaProperty();
        if (!$metaProperty) {
            return;
        }
        $options = $this->metaEntityFactory->getEntityOptions();
        $commandInfo->getIo()->listing($options);
        $question = new Question('Target entity', $metaProperty->getTargetEntity());
        $question->setAutocompleterValues($options);
        $targetEntityChoice = $commandInfo->getIo()->askQuestion($question);

        //'targetEntityChoice' might be MetaEntity because of the default value on metaAttribute.
        if ($targetEntityChoice instanceof MetaEntityInterface) {
            $metaProperty->setTargetEntity($targetEntityChoice);
        //If one of the options was chosen, then we can derive the metaEntity from that.
        } elseif($targetMetaEntity = $this->metaEntityFactory->getMetaEntityByChosenOption($targetEntityChoice)) {
            $metaProperty->setTargetEntity($targetMetaEntity);
        //Otherwise, if something new was entered, we compose the targetEntity by using the shortcutNotation
        } else {
            $metaProperty->setTargetEntity($this->metaEntityFactory->createByShortcutNotation($targetEntityChoice));
        }
    }
}