<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Generator\GeneratorConfig;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandInfo
{
    /** @var InputInterface */
    public $input;
    /** @var OutputInterface */
    public $output;
    /** @var GeneratorConfig */
    public $generatorConfig;
    /** @var MetaEntity */
    public $metaEntity;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        GeneratorConfig $generatorConfig,
        MetaEntity $metaEntity = null
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->generatorConfig = $generatorConfig;
        $this->metaEntity = $metaEntity;
    }

    public function getIo()
    {

    }
}