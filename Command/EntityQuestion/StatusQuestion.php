<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractPrimitiveProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;

class StatusQuestion implements EntityQuestionInterface
{
    public function addActions(CommandInfo $commandInfo, array &$actions) {
        $actions['Show info'] = function() use($commandInfo) { $this->showInfo($commandInfo); };
    }

    public function doQuestion(CommandInfo $commandInfo)
    {
        //Do nothing
    }

    public function showInfo(CommandInfo $commandInfo)
    {
        $commandInfo->getIo()->text(sprintf('<info>Current Entity:</info> %s', $commandInfo->getMetaEntity()->getName()));
        $propertyOutputs = [];
        foreach ($commandInfo->getMetaEntity()->getProperties() as $property) {
            $validationNames = [];
            foreach ($property->getValidations() as $validation) {
                $validationNames[] = $validation->getClassShortName();
            }
            $propertyOutputs[] = [
                $property->getName(),
                $property->getOrmType()
                    . ($property instanceof AbstractRelationshipProperty ? '<comment> ['.$property->getTargetEntity().']</comment>' : '')
                    . ($property instanceof AbstractPrimitiveProperty && $property->isId() ? ' [<comment>id</comment>]' : '')
                    . ($commandInfo->getMetaEntity()->getDisplayProperty() === $property ? ' [<comment>display field</comment>]' : '')
                    . ($property instanceof AbstractPrimitiveProperty && $property->isNullable() ? ' <comment>[nullable]</comment>' : '')
                ,
                implode(', ', $validationNames),
            ];
        }
        $commandInfo->getIo()->table(['Property', 'Type', 'Validations'], $propertyOutputs);
    }
}