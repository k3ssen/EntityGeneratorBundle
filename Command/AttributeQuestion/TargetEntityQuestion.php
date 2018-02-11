<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Helper\EntityFinder;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttribute;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;
use Symfony\Component\Console\Question\Question;

class TargetEntityQuestion implements AttributeQuestionInterface
{
    /** @var EntityFinder */
    protected $entityFinder;

    protected $attributeName;

    protected $bundles;

    public function __construct(array $bundles, array $attributes, string $attributeName, EntityFinder $entityFinder)
    {
        if (!array_key_exists($attributeName, $attributes)) {
            throw new \InvalidArgumentException(sprintf('attribute name "%s" has not been defined in the "attributes" configuration', $attributeName));
        }
        $this->bundles = $bundles;
        $this->attributeName = $attributeName;
        $this->entityFinder = $entityFinder;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function doQuestion(CommandInfo $commandInfo, MetaAttribute $metaAttribute)
    {
        /** @var AbstractRelationshipProperty $metaProperty */
        $metaProperty = $metaAttribute->getMetaProperty();
        if (!$metaProperty) {
            return;
        }
        $options = $this->entityFinder->getExistingEntities();
        $commandInfo->getIo()->listing($options);
        $question = new Question('Target entity', $metaProperty->getTargetEntity());
        $question->setAutocompleterValues($options);
        $targetEntity = $commandInfo->getIo()->askQuestion($question);

        if ($namespace = array_search($targetEntity, $options, true)) {
            $namespaceWithoutEntityName = str_replace('\\'.$targetEntity, '', $namespace);
            $metaProperty->setTargetEntityNamespace($namespaceWithoutEntityName);
            $metaProperty->setTargetEntity($targetEntity);
        } else {
            $this->setTargetEntityAndNamespace($commandInfo, $metaAttribute, $targetEntity);
        }
    }

    protected function setTargetEntityAndNamespace(CommandInfo $commandInfo, MetaAttribute $metaAttribute, string $targetEntity)
    {
        /** @var AbstractRelationshipProperty $metaProperty */
        $metaProperty = $metaAttribute->getMetaProperty();
        $namespace = null;

        //Check for bundle
        if(strpos($targetEntity, ':') !== false) {
            $entityParts = explode(':', str_replace('::', ':', $targetEntity));
            $entityBundleName = array_shift($entityParts);
            $targetEntity =  implode('/', $entityParts);

            foreach ($this->bundles as $bundleName => $bundleNamespace) {
                if ($entityBundleName === $bundleName) {
                    $namespace = $bundleNamespace;
                }
            }
            //The default 'App' namespace isn't a bundle, but if you're currently creating an entity in a different bundle
            //You might want to specify 'App' to prevent using that same bundle instead of the App-namespace
            if (!$namespace && $entityBundleName === 'App') {
                $namespace = 'App\\Entity';
            } elseif (!$namespace) {
                $commandInfo->getIo()->error(sprintf('No bundle with name "%s" could be found.', $entityBundleName));
                $this->doQuestion($commandInfo, $metaAttribute);
            }
        }
        //Check for subdirectory
        if(strpos($targetEntity, '/') !== false) {
            $entityParts = explode('/', $targetEntity);
            $targetEntity = array_pop($entityParts);
            $namespace = ($namespace ?: $commandInfo->getMetaEntity()->getNamespace()) . '\\' . implode('\\', $entityParts);
        }
        if ($namespace) {
            $metaProperty->setTargetEntityNamespace($namespace);
        }
        $metaProperty->setTargetEntity($targetEntity);
    }
}