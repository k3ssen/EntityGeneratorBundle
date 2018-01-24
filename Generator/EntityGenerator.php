<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;

class EntityGenerator
{
    /** @var \Twig_Environment */
    protected $twigEngine;

    //TODO: moet kunnen worden overschreven
    protected $entityDirPath = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Entity'.DIRECTORY_SEPARATOR;

    public function __construct(\Twig_Environment $twigEngine, $rootDir)
    {
        $this->twigEngine = $twigEngine;

        $this->entityDirPath = $rootDir . DIRECTORY_SEPARATOR . 'Entity'.DIRECTORY_SEPARATOR;
    }

    public function createEntity($dataInput = null)
    {
        $entityName = 'ExampleTestEntity';
        //TODO: moet kunnen worden overschreven
        $entityFileData = $this->twigEngine->render('entity/entity.php.twig', [
            'meta_entity' => static::createExampleMetaEntity(),
        ]);

        file_put_contents($this->entityDirPath . $entityName . '.php', $entityFileData);
    }

    public static function createExampleMetaEntity()
    {
        $metaEntity = new MetaEntity('Library');

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