<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\PropertyQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\AttributesExpressionLanguageProvider;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\AttributeQuestion\AttributeQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaAttribute;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaAttributeFactory;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class AttributesQuestion implements PropertyQuestionInterface
{
    /** @var MetaAttributeFactory */
    protected $metaAttributeFactory;

    /** @var ExpressionLanguage */
    protected $expressionLanguage;

    /** @var ContainerInterface */
    protected $container;

    public function __construct(
        MetaAttributeFactory $metaAttributeFactory,
        AttributesExpressionLanguageProvider $attributesExpressionLanguageProvider,
        ContainerInterface $container
    ) {
        $this->metaAttributeFactory = $metaAttributeFactory;
        $this->container = $container;
        $this->expressionLanguage = new ExpressionLanguage(null, [$attributesExpressionLanguageProvider]);

    }

    public function doQuestion(CommandInfo $commandInfo, AbstractProperty $metaProperty = null)
    {
        foreach ($metaProperty->getMetaAttributes() as $metaAttribute) {
            if ($metaAttribute->getValueIsSetByUserInput()) {
                continue;
            }
            if ($condition = $metaAttribute->getCondition()) {
                $conditionResult = $this->evaluate($commandInfo, $metaAttribute, $condition);
                if ($conditionResult === false) {
                    continue;
                }
            }

            if ($serviceClass = $metaAttribute->getQuestionService()) {
                $this->doQuestionService($commandInfo, $metaAttribute, $serviceClass);
            } elseif ($metaAttribute->isBool()) {
                $value = $commandInfo->getIo()->confirm($metaAttribute->getQuestion(), $metaAttribute->getValue() !== false);
                $metaAttribute->setValue($value);
            } else {
                $this->doSingleAttributeQuestion($commandInfo, $metaAttribute);
            }
        }
    }

    protected function doQuestionService(CommandInfo $commandInfo, MetaAttribute $metaAttribute, string $serviceClass)
    {
        $questionService = $this->container->get($serviceClass);
        if (!$questionService instanceof AttributeQuestionInterface) {
            throw new \RuntimeException(sprintf('Service "%s" does not implement "%s"', $questionService, AttributeQuestionInterface::class));
        }
        $questionService->doQuestion($commandInfo, $metaAttribute);
    }

    protected function doSingleAttributeQuestion(CommandInfo $commandInfo, MetaAttribute $metaAttribute)
    {
        $question = $metaAttribute->getQuestion() . ($metaAttribute->isNullable() ? ' (optional)': '');

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

            if ($validation = $metaAttribute->getValidation()) {
                $validationResult = $this->evaluate($commandInfo, $metaAttribute, $validation);
                if (!$validationResult) {
                    throw new \InvalidArgumentException(sprintf('Value evaluated false by validation expression "%s"', $metaAttribute->getValidation()));
                }
            }
            return $value;
        });
        $metaAttribute->setValue($value);
    }

    protected function evaluate(CommandInfo $commandInfo, MetaAttribute $metaAttribute, string $expression)
    {
        return $this->expressionLanguage->evaluate($expression, [
            'this' => $metaAttribute,
            'metaProperty' => $metaAttribute->getMetaProperty(),
            'metaEntity' => $metaAttribute->getMetaProperty()->getMetaEntity(),
            'generatorConfig' => $commandInfo->getGeneratorConfig(),
        ]);
    }
}