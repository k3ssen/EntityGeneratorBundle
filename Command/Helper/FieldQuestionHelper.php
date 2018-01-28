<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Types\Type;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractRelationshipProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\MetaPropertyFactory;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

class FieldQuestionHelper extends QuestionHelper
{
    use QuestionTrait;
    /** @var EntityFinder */
    protected $entityFinder;
    /** @var MetaPropertyFactory */
    protected $metaPropertyFactory;
    /** @var FieldAttributesQuestionHelper */
    protected $fieldAttributesQuestionHelper;
    /** @var string */
    protected $guessedEntity;

    public function __construct(
        EntityFinder $entityFinder,
        MetaPropertyFactory $metaPropertyFactory,
        FieldAttributesQuestionHelper $fieldAttributesQuestionHelper

    ) {
        $this->entityFinder = $entityFinder;
        $this->metaPropertyFactory = $metaPropertyFactory;
        $this->fieldAttributesQuestionHelper = $fieldAttributesQuestionHelper;
    }

    public function makeField(CommandInfo $commandInfo, AbstractProperty $metaProperty = null): ?AbstractProperty
    {
        $this->commandInfo = $commandInfo;
        if ($metaProperty) {
            $commandInfo->output->writeln(sprintf('<info>Currently continuing with field %s</info>', $metaProperty->getName()));
            $this->fieldAttributesQuestionHelper->setAttributes($commandInfo, $metaProperty);
            return $metaProperty;
        }
        $fieldName = $this->askQuestion(new Question('<info>New field name</info> (press <return> to stop adding fields): '));
        if (!$fieldName) {
            return null;
        }
        $metaProperty = $this->askFieldType($fieldName);

        $this->fieldAttributesQuestionHelper->setAttributes($commandInfo, $metaProperty);

        return $metaProperty;
    }

    protected function askFieldType(string $fieldName): AbstractProperty
    {
        $typeOptions = array_keys($this->metaPropertyFactory->getTypes());
        $this->outputOptions($typeOptions);

        $defaultType = $this->guessFieldType($fieldName);

        $question = new Question(sprintf('<info>Field type</info> [<comment>%s</comment>]: ', $defaultType), $defaultType);
        $question->setNormalizer(function($type) {
            return $this->metaPropertyFactory->getTypeAliases()[$type] ?? $type;
        });
        $question->setValidator(function($type) use ($typeOptions) {
            if (!in_array($type, $typeOptions, true)) {
                throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
            }
            return $type;
        });
        $question->setAutocompleterValues(array_merge($typeOptions, $this->metaPropertyFactory->getTypeAliases()));

        $type = $this->askQuestion($question);
        $metaProperty = $this->metaPropertyFactory->getMetaPropertyByType($this->commandInfo->metaEntity, $type, $fieldName);
        $this->commandInfo->metaEntity->addProperty($metaProperty);

        if ($metaProperty instanceof AbstractRelationshipProperty && $this->guessedEntity) {
            $metaProperty->setTargetEntity($this->guessedEntity);
            $metaProperty->setTargetEntityNamespace(array_search($this->guessedEntity, $this->entityFinder->getExistingEntities()));
            $this->guessedEntity = null;
        }

        return $metaProperty;
    }

    protected function guessFieldType(string $fieldName): string
    {
        $columnName = Inflector::tableize($fieldName);
        $lastThreeChars = substr($columnName, -3);
        $lastFourChars = substr($columnName, -4);
        $lastFiveChars = substr($columnName, -5);
        if ($lastThreeChars === '_at' || $lastThreeChars === '_on') {
            return Type::DATETIME;
        } if ($lastFiveChars === 'count') {
            return Type::INTEGER;
        } if (0 === strpos($columnName, 'is_') || 0 === strpos($columnName, 'has_')) {
            return Type::BOOLEAN;
        } if ($lastFourChars === 'date') {
            return Type::DATE;
        } if ($lastThreeChars === '_id' || $this->guessFieldIsManyToOne($columnName)) {
            return MetaPropertyFactory::MANY_TO_ONE;
        } if (in_array($columnName, ['summary', 'description', 'text'], true)) {
            return Type::TEXT;
        } if ($lastFiveChars === 'price') {
            return Type::DECIMAL;
        } if ($this->guessFieldIsOneToMany($columnName)) {
            return MetaPropertyFactory::ONE_TO_MANY;
        }
        return Type::STRING;
    }

    protected function guessFieldIsOneToMany(string $name): bool
    {
        return $this->guessEntity(Inflector::singularize($name)) !== null;
    }

    protected function guessFieldIsManyToOne(string $name): bool
    {
        return $this->guessEntity($name) !== null;
    }

    protected function guessEntity(string $name): ?string
    {
        return $this->guessedEntity = $this->entityFinder->findEntityClass($name);
    }
}