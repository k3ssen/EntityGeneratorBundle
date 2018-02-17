<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command;

use Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion\EntityQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Generator\EntityGenerator;
use Kevin3ssen\EntityGeneratorBundle\MetaData\ExistingEntityToMetaEntityReader;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntityFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EntityAlterCommand extends Command
{
    protected static $defaultName = 'entity:alter';

    /** @var ExistingEntityToMetaEntityReader */
    protected $existingEntityToMetaEntityReader;

    /** @var MetaEntityFactory */
    protected $metaEntityFactory;

    /** @var EntityGenerator */
    protected $entityGenerator;

    /** @var iterable|EntityQuestionInterface[] */
    protected $entityQuestions;

    public function __construct(
        ?string $name = null,
        ExistingEntityToMetaEntityReader $existingEntityToMetaEntityReader,
        MetaEntityFactory $metaEntityFactory,
        EntityGenerator $entityGenerator,
        iterable $entityQuestions
    ) {
        parent::__construct($name);
        $this->existingEntityToMetaEntityReader = $existingEntityToMetaEntityReader;
        $this->metaEntityFactory = $metaEntityFactory;
        $this->entityGenerator = $entityGenerator;
        $this->entityQuestions = $entityQuestions;
    }

    protected function configure()
    {
        $this->setDescription('Alter an existing entity')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('savepoint', 's', InputOption::VALUE_NONE, false)
            ->addOption('revert', 'r', InputOption::VALUE_NONE, false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandInfo = new CommandInfo($input, $output);

        $commandInfo->getIo()->title('Alter entity');
        $choices = $this->metaEntityFactory->getEntityOptions();
        if (count($choices) === 0) {
            $commandInfo->getIo()->error('No entities found; Use \'entity:generate\' instead to add new entities.');
            return;
        } else {

            $commandInfo->getIo()->warning('Altering an entity will result in the file being overwritten. 
Because the generator cannot process information outside its scope, 
some information might be lost, such as custom alterations to the class.

If you want to preserve all information, use \'entity:append\' instead.');
        }
        $choice = $commandInfo->getIo()->choice('Entity', $choices, $input->getArgument('entity'));
        $metaEntity = $this->metaEntityFactory->getMetaEntityByChosenOption($choice);

        $this->existingEntityToMetaEntityReader->extractExistingClassToMetaEntity($metaEntity);
        $commandInfo->setMetaEntity($metaEntity);

        $metaEntity = $this->getMetaEntity($commandInfo);

        $affectedFiles = $this->entityGenerator->createEntity($metaEntity);

        foreach ($affectedFiles as $fileName) {
            $commandInfo->getIo()->success(sprintf('Created/Updated file %s', $fileName));
        }
    }

    protected function getMetaEntity(CommandInfo $commandInfo): ?MetaEntity
    {
        if ($useSavePoint = $commandInfo->getInput()->getOption('savepoint')) {
            $commandInfo->loadMetaEntityFromTemporaryFile();
            $commandInfo->getIo()->title(sprintf('Use savepoint entity "%s"', (string) $commandInfo->getMetaEntity()));
        }
        $actions = [];
        foreach ($this->entityQuestions as $entityQuestion) {
            if (!$entityQuestion instanceof EntityQuestionInterface) {
                throw new \LogicException(sprintf('Service "%s" is used as entityQuestion, but does not implement %s', get_class($entityQuestion), EntityQuestionInterface::class));
            }
            $entityQuestion->addActions($commandInfo, $actions);
            if (!$useSavePoint) {
                $entityQuestion->doQuestion($commandInfo);
                $commandInfo->saveTemporaryFile();
            }
        }
        $actions['All done! Generate entity!'] = null;
        do {
            $chosenAction = $commandInfo->getIo()->choice('Next action', array_keys($actions));
            $nextAction = $actions[$chosenAction] ?? null;
            if ($nextAction) {
                $nextAction();
                $commandInfo->saveTemporaryFile();
            }
        } while ($nextAction);

        return $commandInfo->getMetaEntity();
    }
}
