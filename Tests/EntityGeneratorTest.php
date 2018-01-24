<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Tests;

use Kevin3ssen\EntityGeneratorBundle\Generator\EntityGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData;

class EntityGeneratorTest extends TestCase
{
    public function testEntityContentResult()
    {
        $this->assertEquals(
            file_get_contents(__DIR__.'/expected_results/Library.txt'),
            $this->getEntityGenerator()->getEntityContent(static::createExampleMetaEntity())
        );
    }

    protected function getEntityGenerator(): EntityGenerator
    {
        $fileLocator = FileLocator::class;
        $fileLocator = $this->createMock($fileLocator);
        $fileLocator->expects($this->any())->method('locate')->willReturn(__DIR__.'/../templates');

        return new EntityGenerator($fileLocator);
    }

    protected static function createExampleMetaEntity()
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