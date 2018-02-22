<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\MetaData\EntityAnnotation;


use K3ssen\EntityGeneratorBundle\MetaData\MetaEntityInterface;

class OrmEntityAnnotation implements AnnotationInterface
{
    protected $metaEntity;

    public function __construct(MetaEntityInterface $metaEntity)
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
        if ($this->metaEntity->hasCustomRepository()) {
            return [
                'repositoryClass' => $this->metaEntity->getRepositoryFullClassName(),
            ];
        }
        return [];
    }
}