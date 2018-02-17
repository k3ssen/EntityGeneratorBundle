<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EvaluationTrait;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttribute;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttributeFactory;

class BasicAttributeQuestion implements AttributeQuestionInterface
{
    use EvaluationTrait;

    protected $attributeName;
    protected $requirementExpression;
    protected $validationExpression;

    public function __construct(
        MetaAttributeFactory $metaAttributeFactory,
        string $attributeName,
        string $requirementExpression = null,
        string $validationExpression = null
    ) {
        if (!$metaAttributeFactory->attributeExists($attributeName)) {
            throw new \InvalidArgumentException(sprintf('attribute name "%s" has not been defined in the "attributes" configuration', $attributeName));
        }
        $this->attributeName = $attributeName;
        $this->requirementExpression = $requirementExpression;
        $this->validationExpression = $validationExpression;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function doQuestion(CommandInfo $commandInfo, MetaAttribute $metaAttribute)
    {
        if ($metaAttribute->isBool()) {
            $value = $commandInfo->getIo()->confirm(ucfirst($metaAttribute->getName()), $metaAttribute->getValue() !== false);
            $metaAttribute->setValue($value);
            return;
        }

        $question = ucfirst($metaAttribute->getName()) . ($metaAttribute->isNullable() ? ' (optional)': '');
        $value = $commandInfo->getIo()->ask($question, $metaAttribute->getValue(), function ($value) use ($metaAttribute, $commandInfo) {
            if (!$metaAttribute->isNullable() && $value === null) {
                throw new \InvalidArgumentException('This value cannot be null');
            }
            if ($metaAttribute->isInt()) {
                if ($value !== null && !is_numeric($value)) {
                    throw new \InvalidArgumentException('This value must be a number');
                }
                $value = (int) $value;
            }

            if ($validation = $this->validationExpression) {
                $validationResult = $this->evaluateMetaAttribute($metaAttribute, $validation);
                if (!$validationResult) {
                    throw new \InvalidArgumentException(sprintf('Value evaluated false by validation expression "%s"', $metaAttribute->getValidation()));
                }
            }
            return $value;
        });
        $metaAttribute->setValue($value);
    }
}