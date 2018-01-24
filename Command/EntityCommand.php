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
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData;

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

        $metaEntity = static::createExampleMetaEntity();

        $entityFile = $this->entityGenerator->createEntity($metaEntity);

        $io->success(sprintf('Generated new entity in file %s', $entityFile));
    }

    public static function createExampleMetaEntity()
    {
        $metaEntity = new MetaData\MetaEntity('Library', 'EntityGeneratorBundle', 'Admin');

        $title = (new MetaData\Property\StringProperty($metaEntity, 'title'));

        $metaEntity->setDisplayProperty($title);

        (new MetaData\Property\IntegerProperty($metaEntity, 'numberOfSomething'))
            ->setNullable(true)
            ->setLength(6);

        (new MetaData\Property\ManyToOneProperty($metaEntity, 'country'))
            ->setNullable(true)
        ;

        (new MetaData\Property\OneToManyProperty($metaEntity, 'books'))
            ->setTargetEntityNamespace('SomeOtherBundle\\Entity')
            ->setNullable(true)
        ;

        return $metaEntity;
    }
}
