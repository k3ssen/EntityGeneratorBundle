<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;

class DisplayFieldQuestion implements EntityQuestionInterface
{
    public function addActions(CommandInfo $commandInfo, array &$actions) {
        $actions['Edit display field'] = function() use($commandInfo) { $this->doQuestion($commandInfo); };
    }

    public function doQuestion(CommandInfo $commandInfo)
    {
        if (!$commandInfo->getGeneratorConfig()->askDisplayField()) {
            return;
        }
        $propertyOptions = ['' => null];
        foreach ($commandInfo->getMetaEntity()->getProperties() as $property) {
            if (in_array($property->getReturnType(), ['string', 'int'], true)) {
                $propertyOptions[$property->getName()] = $property;
            }
        }
        if (count($propertyOptions) === 1) {
            $commandInfo->getIo()->warning('There are no properties suitable for using as display field automatically.');
            return;
        }
        $defaultDisplayField = $commandInfo->getMetaEntity()->getDisplayProperty();
        $answer = $commandInfo->getIo()->choice('Display field (optional)', array_keys($propertyOptions), $defaultDisplayField ? (string) $defaultDisplayField : null);
        $property = $propertyOptions[$answer] ?? $answer;
        $commandInfo->getMetaEntity()->setDisplayProperty($property === '' ? null : $property);
    }
}