<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command;

use Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion\FieldsQuestion;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EntityFinder;
use Kevin3ssen\EntityGeneratorBundle\Generator\EntityAppender;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class EntityAppendCommand extends Command
{
    protected static $defaultName = 'entity:append';

    /** @var EntityAppender */
    protected $entityAppender;

    /** @var EntityFinder */
    protected $entityFinder;

    /** @var $fieldQuestion */
    protected $fieldsQuestion;

    protected $bundles;

    public function __construct(
        ?string $name = null,
        EntityAppender $entityReader,
        EntityFinder $entityFinder,
        FieldsQuestion $fieldsQuestion,
        array $bundles
    ) {
        parent::__construct($name);
        $this->entityAppender = $entityReader;
        $this->entityFinder = $entityFinder;
        $this->fieldsQuestion = $fieldsQuestion;
        $this->bundles = $bundles;
    }

    protected function configure()
    {
        $this->setDescription('Read an entity')
            ->addOption('savepoint', 's', InputOption::VALUE_NONE, false)
            ->addOption('revert', 'r', InputOption::VALUE_NONE, false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandInfo = new CommandInfo($input, $output);
        if ($input->getOption('revert')) {
            $commandInfo->loadMetaEntityFromTemporaryFile();
            $metaEntity = $commandInfo->getMetaEntity();
            $this->revertFileMetaEntity($metaEntity);
            $commandInfo->getIo()->success(sprintf('File for entity "%s" has been reverted', $metaEntity));
            return;
        }
        if ($input->getOption('savepoint')) {
            $commandInfo->loadMetaEntityFromTemporaryFile();
            $pseudoMetaEntity = $commandInfo->getMetaEntity();
        } else {
            $choices = $this->entityFinder->getExistingEntities();
            $entityFullClassName = $commandInfo->getIo()->choice('Entity', $choices);

            $pseudoMetaEntity = $this->createPseudoMetaEntity($entityFullClassName);
            $commandInfo->setMetaEntity($pseudoMetaEntity);
        }
        $this->fieldsQuestion->doQuestion($commandInfo);
        $this->checkExistingFields($commandInfo);
        $commandInfo->saveTemporaryFile();
        $this->backupFile($pseudoMetaEntity);
        $entityFile = $this->entityAppender->appendFields($pseudoMetaEntity);
        $commandInfo->getIo()->success(sprintf('Updated entity in file %s', $entityFile));
        $commandInfo->getIo()->note('If the result is not what you wanted, you can revert this change by using the command "entity:append --revert"');
    }

    protected function backupFile(MetaEntity $metaEntity)
    {
        $reflector = new \ReflectionClass($metaEntity->getFullClassName());
        $content = file_get_contents($reflector->getFileName());
        $temp = sys_get_temp_dir(). '/entity_backup';
        file_put_contents($temp, $content);
    }

    protected function revertFileMetaEntity(MetaEntity $metaEntity)
    {
        $reflector = new \ReflectionClass($metaEntity->getFullClassName());
        $temp = sys_get_temp_dir(). '/entity_backup';
        if (!file_exists($temp)) {
            throw new FileNotFoundException('No backup file available.');
        }
        $content = file_get_contents($temp);
        file_put_contents($reflector->getFileName(), $content);
    }

    protected function checkExistingFields(CommandInfo $commandInfo)
    {
        $metaEntity = $commandInfo->getMetaEntity();
        $reflector = new \ReflectionClass($metaEntity->getFullClassName());
        foreach($reflector->getProperties() as $reflectorProperty) {
            foreach ($metaEntity->getProperties() as $metaProperty) {
                if ($metaProperty->getName() === $reflectorProperty->getName()) {
                    $commandInfo->getIo()->error(sprintf('The property "%s" is already defined in entity "%s".', $metaProperty, $metaEntity));
                    $doEdit = $commandInfo->getIo()->confirm('Edit this field? Choose "no" to remove this field');
                    if ($doEdit) {
                        $this->fieldsQuestion->editField($commandInfo, $metaProperty);
                    } else {
                        $this->fieldsQuestion->removeField($commandInfo, $metaProperty);
                    }
                    $this->checkExistingFields($commandInfo);
                    return;
                }
            }
        };
    }

    protected function createPseudoMetaEntity(string $entityFullClassName): MetaEntity
    {
        $reflector = new \ReflectionClass($entityFullClassName);
        $pseudoMetaEntity = new MetaEntity($reflector->getShortName());

        $namespaceParts = explode('\\Entity\\', $entityFullClassName);
        $pseudoMetaEntity->setBundle($this->getBundleName($namespaceParts[0]));

        if (strpos('\\', $namespaceParts[1])) {
            $dirAndNameParts = explode('\\', $namespaceParts[1]);
            $entityName = array_pop($dirAndNameParts);
            if ($entityName !== $pseudoMetaEntity->getName()) {
                throw new \LogicException(sprintf('
                    Tried to retrieve bundle, subdirectory and entityName from "%s", but result is incorrect
                    Expected entity name "%s", but got "%s"
                ', $entityFullClassName, $pseudoMetaEntity->getName(), $entityName));
            }
            $pseudoMetaEntity->setSubDir(implode('/', $dirAndNameParts));
        }
        return $pseudoMetaEntity;
    }

    protected function getBundleName(string $namespaceBeforeEntity): ?string
    {
        foreach ($this->bundles as $bundleName => $bundleNamespace) {
            if ($namespaceBeforeEntity === $bundleNamespace) {
                return $bundleName;
            }
        }
        return null;
    }
}
