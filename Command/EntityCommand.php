<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\EntityQuestionnaire;
use Kevin3ssen\EntityGeneratorBundle\Generator\GeneratorConfig;
use Kevin3ssen\EntityGeneratorBundle\Generator\EntityGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class EntityCommand extends Command
{
    protected static $defaultName = 'entity:generate';

    /** @var EntityGenerator */
    protected $entityGenerator;
    /** @var GeneratorConfig */
    protected $generatorConfig;
    /** @var EntityQuestionnaire */
    protected $entityQuestionnaire;

    public function __construct(
        ?string $name = null,
        EntityGenerator $entityGenerator,
        GeneratorConfig $generatorConfig,
        EntityQuestionnaire $entityQuestionHelper
    ) {
        parent::__construct($name);
        $this->entityGenerator = $entityGenerator;
        $this->generatorConfig = $generatorConfig;
        $this->entityQuestionnaire = $entityQuestionHelper;
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

        $commandInfo = new CommandInfo($input, $output, $this->generatorConfig);

        if ($input->getOption('savepoint')) {
            $commandInfo->loadMetaEntityFromTemporaryFile();
        }
        $metaEntity = $this->entityQuestionnaire->makeEntity($commandInfo);

        $entityFile = $this->entityGenerator->createEntity($metaEntity);
        $io->success(sprintf('Generated entity in file %s', $entityFile));

        if ($metaEntity->hasCustomRepository()) {
            $repoFile = $this->entityGenerator->createRepository($metaEntity);
            $io->success(sprintf('Generated repository in file %s', $repoFile));
        }
    }
}
