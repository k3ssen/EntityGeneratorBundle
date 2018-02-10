<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\PropertyQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion\AttributeQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EvaluationTrait;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractProperty;

class AttributesQuestion implements PropertyQuestionInterface
{
    use EvaluationTrait;

    /** @var iterable|AttributeQuestionInterface[] */
    protected $attributeQuestions;

    public function __construct(iterable $attributeQuestions)
    {
        $this->attributeQuestions = $attributeQuestions;
    }

    public function doQuestion(CommandInfo $commandInfo, AbstractProperty $metaProperty = null)
    {
        foreach ($metaProperty->getMetaAttributes() as $metaAttribute) {
            foreach ($this->attributeQuestions as $attributeQuestion) {
                if ($metaAttribute->getName() === $attributeQuestion->getAttributeName()) {
                    $attributeQuestion->doQuestion($commandInfo, $metaAttribute);
                    $commandInfo->saveTemporaryFile();
                }
            }
        }
    }
}