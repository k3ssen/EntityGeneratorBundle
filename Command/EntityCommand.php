<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion\EntityQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Generator\EntityGenerator;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EntityCommand extends Command
{
    protected static $defaultName = 'entity:generate';

    /** @var EntityGenerator */
    protected $entityGenerator;

    /** @var iterable|EntityQuestionInterface[] */
    protected $entityQuestions;

    public function __construct(
        ?string $name = null,
        EntityGenerator $entityGenerator,
        iterable $entityQuestions
    ) {
        parent::__construct($name);
        $this->entityGenerator = $entityGenerator;
        $this->entityQuestions = $entityQuestions;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create an entity')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('savepoint', 's', InputOption::VALUE_NONE, false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $commandInfo = new CommandInfo($input, $output);

        if ($input->getOption('savepoint')) {
            $commandInfo->loadMetaEntityFromTemporaryFile();
        }
        $metaEntity = $this->makeEntity($commandInfo);

        $entityFile = $this->entityGenerator->createEntity($metaEntity);
        $io->success(sprintf('Generated entity in file %s', $entityFile));

        if ($metaEntity->hasCustomRepository()) {
            $repoFile = $this->entityGenerator->createRepository($metaEntity);
            $io->success(sprintf('Generated repository in file %s', $repoFile));
        }
    }

    public function makeEntity(CommandInfo $commandInfo): ?MetaEntity
    {
        $useSavePoint = $commandInfo->getMetaEntity() !== null;
        if ($useSavePoint) {
            $commandInfo->getIo()->title(sprintf('Use savepoint entity "%s"', (string) $commandInfo->getMetaEntity()));
        }
        $actions = [];
        foreach ($this->entityQuestions as $entityQuestion) {
            if (!$entityQuestion instanceof EntityQuestionInterface) {
                throw new LogicException(sprintf('Service "%s" is used as entityQuestion, but does not implement %s', get_class($entityQuestion), EntityQuestionInterface::class));
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
