<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntityFactory;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class EntityQuestionHelper extends QuestionHelper
{
    use QuestionTrait;

    /** @var MetaEntityFactory */
    protected $metaEntityFactory;
    /** @var FieldQuestionHelper */
    protected $fieldTypeQuestionHelper;

    public function __construct(
        MetaEntityFactory $metaEntityFactory,
        FieldQuestionHelper $fieldTypeQuestionHelper
    ) {
        $this->metaEntityFactory = $metaEntityFactory;
        $this->fieldTypeQuestionHelper = $fieldTypeQuestionHelper;
    }

    public function makeEntity(CommandInfo $commandInfo, string $entityName = null): ?MetaEntity
    {
        $this->commandInfo = $commandInfo;
        [$bundle, $subDir, $entityName] = $this->extractFromEntityNameAnswer($entityName);
        $this->askEntityName($entityName);
        $this->askBundle($bundle);
        $this->askSubDir($subDir);
        //TODO: trait-questions
        do {
            $metaProperty = $this->fieldTypeQuestionHelper->makeField($this->commandInfo);
        } while ($metaProperty !== null);

        $this->askDisplayField();
        $this->saveTemporaryFile();
        return $commandInfo->metaEntity;
    }

    public function continueEntity(CommandInfo $commandInfo, MetaEntity $metaEntity)
    {
        $this->commandInfo = $commandInfo;
        $commandInfo->metaEntity = $metaEntity;
        $this->showCurrentOverview();
//        $this->getIo()->section(sprintf('Continue editing entity %s', $metaEntity->getName()));

        foreach ($metaEntity->getProperties() as $property) {
            $propertyOptions[$property->getName()] = $property;
        }
        $this->outputOptions($propertyOptions);
        $question = new Question('<info>Edit property</info> (leave blank for new or no property): ');
        $question->setAutocompleterValues($propertyOptions);
        $propertyChoices = $this->askQuestion($question);

        $this->fieldTypeQuestionHelper->makeField($this->commandInfo, $propertyOptions[$propertyChoices] ?? null);
        do {
            $metaProperty = $this->fieldTypeQuestionHelper->makeField($this->commandInfo);
        } while ($metaProperty !== null);

        $this->askDisplayField();
        $this->saveTemporaryFile();
        return $commandInfo->metaEntity;
    }

    protected function askEntityName(string $entityName = null)
    {
        $entityName = $this->askSimpleQuestion('Entity name', $entityName);
        [$bundle, $subDir, $entityName] = $this->extractFromEntityNameAnswer($entityName);
        $this->commandInfo->metaEntity = $this->metaEntityFactory->createMetaEntity($entityName, $bundle, $subDir);
    }

    /**
     * EntityName could be provided as 'AppBundle:AdminDir/Product'
     * which should resolve as bundle=AppBundle, subDir=AdminDir, entityname=Product
     *
     * @param string|null $entityName
     * @return array in format [bundle, subDir, entityName]
     */
    protected function extractFromEntityNameAnswer(string $entityName = null): array
    {
        if (!$entityName) {
            return [null, null, null];
        }
        $subDir = $bundle = null;
        $entityBundleSplit = explode(':', $entityName);
        if (count($entityBundleSplit) === 2) {
            $bundle = $entityBundleSplit[0];
            $entityName = $entityBundleSplit[1];
        }
        $entityDirSplit = explode(DIRECTORY_SEPARATOR, $entityName);
        if (count($entityDirSplit) > 1) {
            $entityName = array_pop($entityDirSplit);
            $subDir = join(DIRECTORY_SEPARATOR, $entityDirSplit);
        }
        return [$bundle, $subDir, $entityName];
    }

    protected function askBundle(string $bundle = null)
    {
        if (!$this->commandInfo->generatorConfig->askBundle()) {
            return;
        }
        $bundle = $this->askSimpleQuestion('Bundle', $this->commandInfo->metaEntity->getBundle() ?: $bundle);
        $this->commandInfo->metaEntity->setBundle($bundle);
    }

    protected function askSubDir(string $subDir = null)
    {
        if (!$this->commandInfo->generatorConfig->askSubDir()) {
            return;
        }
        $subDir = $this->askSimpleQuestion('Sub directory', $this->commandInfo->metaEntity->getSubDir() ?: $subDir);
        $this->commandInfo->metaEntity->setSubDir($subDir);
    }

    protected function askDisplayField()
    {
        if (!$this->commandInfo->generatorConfig->askDisplayField()) {
            return;
        }
        $propertyOptions = [];
        foreach ($this->commandInfo->metaEntity->getProperties() as $property) {
            if (in_array($property->getReturnType(), ['string', 'int'], true)) {
                $propertyOptions[$property->getName()] = $property;
            }
        }
        if (empty($propertyOptions)) {
            return;
        }
        $this->outputOptions($propertyOptions);
        $question = new Question('<info>Display field</info>[<comment>leave blank for none</comment>]: ', null);
        $question->setAutocompleterValues(array_keys($propertyOptions));
        $propertyName = $this->askQuestion($question);
        $property = $propertyOptions[$propertyName] ?? null;
        if ($property) {
            $this->commandInfo->metaEntity->setDisplayProperty($property);

        }
    }
}