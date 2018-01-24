<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command;

use Kevin3ssen\EntityGeneratorBundle\Generator\EntityGenerator;
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

    public function __construct(?string $name = null, EntityGenerator $entityGenerator)
    {
        parent::__construct($name);
        $this->entityGenerator = $entityGenerator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create an entity')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('bundle', null, InputOption::VALUE_OPTIONAL, 'Bundle you want to create this entity for (defaults to no bundle)', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $entity = $input->getArgument('entity');

        $entityFile = $this->entityGenerator->createEntity($entity);

        $io->success(sprintf('Generated new entity in file %s', $entityFile));
    }
}
