<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Types\Type;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractRelationshipProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaPropertyFactory;

class FieldQuestionHelper
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
    /** @var ValidationQuestionHelper */
    protected $validationQuestionHelper;

    public function __construct(
        EntityFinder $entityFinder,
        MetaPropertyFactory $metaPropertyFactory,
        FieldAttributesQuestionHelper $fieldAttributesQuestionHelper,
        ValidationQuestionHelper $validationQuestionHelper

    ) {
        $this->entityFinder = $entityFinder;
        $this->metaPropertyFactory = $metaPropertyFactory;
        $this->fieldAttributesQuestionHelper = $fieldAttributesQuestionHelper;
        $this->validationQuestionHelper = $validationQuestionHelper;
    }

    public function makeField(CommandInfo $commandInfo, AbstractProperty $metaProperty = null): ?AbstractProperty
    {
        $this->commandInfo = $commandInfo;
        if ($metaProperty) {
            $this->getIo()->text(sprintf('<info>Edit field:</info> %s', $metaProperty->getName()));
            $fieldName = $this->ask('Field name', $metaProperty->getName());
            $metaProperty->setName($fieldName);
        } else {
            $fieldName = $this->ask('New field name (optional)');
            if (!$fieldName) {
                return null;
            }
        }
        $metaProperty = $this->askFieldType($fieldName, $metaProperty);
        $metaProperty->getMetaAttribute('name')->setValueIsSetByUserInput();

        $this->fieldAttributesQuestionHelper->setAttributes($commandInfo, $metaProperty);
        $this->validationQuestionHelper->validationAction($commandInfo, $metaProperty);

        return $metaProperty;
    }

    protected function askFieldType(string $fieldName, AbstractProperty $metaProperty = null): AbstractProperty
    {
        $typeOptions = $this->metaPropertyFactory->getAliasedTypeOptions();
        $defaultType = $metaProperty ? $metaProperty->getOrmType() : $this->guessFieldType($fieldName);
        $type = $this->getIo()->choice('Field type', $typeOptions, $defaultType);
        $type = $typeOptions[$type] ?? $type;
        if ($metaProperty) {
            if ($metaProperty->getOrmType() === $type) {
                return $metaProperty;
            }
            if ($this->commandInfo->metaEntity->getDisplayProperty() === $metaProperty) {
                $this->commandInfo->metaEntity->setDisplayProperty(null);
            }
            $metaProperty->getMetaEntity()->removeProperty($metaProperty);
        }

        $metaProperty = $this->metaPropertyFactory->getMetaProperty($this->commandInfo->metaEntity, $type, $fieldName);

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