<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\EntityAnnotation;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;

class OrmTableAnnotation implements AnnotationInterface
{
    protected $metaEntity;

    public function __construct(MetaEntity $metaEntity)
    {
        $this->metaEntity = $metaEntity;
        $metaEntity->addUsage('Doctrine\ORM\Mapping', 'ORM');
    }

    public function getNamespace(): string
    {
        return 'ORM\Table';
    }

    public function getAnnotationProperties(): ?array
    {
       return [
           'name' => $this->metaEntity->getTableName(),
       ];
    }
}