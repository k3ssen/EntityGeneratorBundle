<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\PropertyQuestion;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Types\Type;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityFactory;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaPropertyFactory;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;

class NameAndTypeQuestion implements PropertyQuestionInterface
{
    /** @var MetaEntityFactory */
    protected $metaEntityFactory;
    /** @var MetaPropertyFactory */
    protected $metaPropertyFactory;
    /** @var MetaEntity */
    protected $guessedEntity;

    public function __construct(
        MetaEntityFactory $metaEntityFactory,
        MetaPropertyFactory $metaPropertyFactory
    ) {
        $this->metaPropertyFactory = $metaPropertyFactory;
        $this->metaEntityFactory = $metaEntityFactory;
    }

    public function doQuestion(CommandInfo $commandInfo, AbstractProperty $metaProperty = null)
    {
        if ($metaProperty) {
            $fieldName = $commandInfo->getIo()->ask('Field name', $metaProperty->getName());
            $metaProperty->setName($fieldName);
        } else {
            $fieldName = $commandInfo->getIo()->ask('Field name (press <comment>[enter]</comment> to stop)');
            if (!$fieldName) {
                return;
            }
        }
        $this->askFieldType($commandInfo, $fieldName, $metaProperty);
    }

    protected function askFieldType(CommandInfo $commandInfo, string $fieldName, AbstractProperty $metaProperty = null)
    {
        $typeOptions = $this->metaPropertyFactory->getAliasedTypeOptions();
        $defaultType = $metaProperty ? $metaProperty->getOrmType() : $this->guessFieldType($fieldName);
        $type = $commandInfo->getIo()->choice('Field type', $typeOptions, $defaultType);
        $type = $typeOptions[$type] ?? $type;
        if ($metaProperty) {
            if ($metaProperty->getOrmType() === $type) {
                return;
            }
            if ($commandInfo->getMetaEntity()->getDisplayProperty() === $metaProperty) {
                $commandInfo->getMetaEntity()->setDisplayProperty(null);
            }
            $metaProperty->getMetaEntity()->removeProperty($metaProperty);
        }

        $metaProperty = $this->metaPropertyFactory->getMetaProperty($commandInfo->getMetaEntity(), $type, $fieldName);

        if ($metaProperty instanceof AbstractRelationshipProperty && $this->guessedEntity) {
            $metaProperty->setTargetEntity($this->guessedEntity);
            $this->guessedEntity = null;
        }
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

    protected function guessEntity(string $name): ?MetaEntity
    {
        $columnName = Inflector::tableize($name);
        foreach ($this->metaEntityFactory->getEntityOptions() as $metaEntityOption) {
            if ($columnName === Inflector::tableize($metaEntityOption->getName())) {
                return $this->guessedEntity = $metaEntityOption;
            }
        }
        return null;
    }
}