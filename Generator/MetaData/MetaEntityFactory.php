<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData;

use Kevin3ssen\EntityGeneratorBundle\Generator\GeneratorConfig;

class MetaEntityFactory
{
    /** @var GeneratorConfig */
    protected $generatorConfig;

    public function __construct(GeneratorConfig $generatorConfig)
    {
        $this->generatorConfig = $generatorConfig;
    }

    public function createMetaEntity(string $name, string $bundle = null, string $subDir = null): ?MetaEntity
    {
        return (new MetaEntity($name, $bundle, $subDir))
            ->setUseCustomRepository($this->generatorConfig->autoGenerateRepository())
        ;
    }
}