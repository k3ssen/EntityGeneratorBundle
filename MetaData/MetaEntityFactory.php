<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

class MetaEntityFactory
{
    /** @var bool */
    protected $autoGenerateRepository;

    public function __construct(?bool $autoGenerateRepository)
    {
        $this->autoGenerateRepository = $autoGenerateRepository;
    }

    public function createMetaEntity(string $name, string $bundle = null, string $subDir = null): ?MetaEntity
    {
        return (new MetaEntity($name, $bundle, $subDir))
            ->setUseCustomRepository($this->autoGenerateRepository)
        ;
    }
}