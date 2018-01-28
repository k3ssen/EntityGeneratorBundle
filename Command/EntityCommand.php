<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EntityQuestionHelper;
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
    /** @var EntityQuestionHelper */
    protected $entityQuestionHelper;

    public function __construct(
        ?string $name = null,
        EntityGenerator $entityGenerator,
        GeneratorConfig $generatorConfig,
        EntityQuestionHelper $entityQuestionHelper
    ) {
        parent::__construct($name);
        $this->entityGenerator = $entityGenerator;
        $this->generatorConfig = $generatorConfig;
        $this->entityQuestionHelper = $entityQuestionHelper;
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
            $temp = sys_get_temp_dir(). '/last_metadata';
            if (file_exists($temp)) {
                $metaData = file_get_contents($temp);
                $metaEntity = unserialize($metaData);
                $metaEntity = $this->entityQuestionHelper->continueEntity($commandInfo, $metaEntity);
            } else {
                throw new FileNotFoundException('No savepoint file found.');
            }
        } else {
            $entity = $input->getArgument('entity');
            $metaEntity = $this->entityQuestionHelper->makeEntity($commandInfo, $entity);
        }

        $entityFile = $this->entityGenerator->createEntity($metaEntity);
        $io->success(sprintf('Generated entity in file %s', $entityFile));

        if ($metaEntity->hasCustomRepository()) {
            $repoFile = $this->entityGenerator->createRepository($metaEntity);
            $io->success(sprintf('Generated repository in file %s', $repoFile));
        }
    }
}
