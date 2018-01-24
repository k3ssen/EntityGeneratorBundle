<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\EntityAnnotation;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;

class OrmEntityAnnotation implements AnnotationInterface
{
    protected $metaEntity;

    public function __construct(MetaEntity $metaEntity)
    {
        $this->metaEntity = $metaEntity;
        $metaEntity->addUsage('Doctrine\ORM\Mapping', 'ORM');
    }

    public function getNamespace(): string
    {
        return 'ORM\Entity';
    }

    public function getAnnotationProperties(): ?array
    {
       return [
           'repositoryClass' => $this->metaEntity->getRepositoryFullClassName(),
       ];
    }
}