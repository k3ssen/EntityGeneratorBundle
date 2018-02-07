<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;

class BundleQuestion implements EntityQuestionInterface
{
    use NameExtractionTrait;

    public function addActions(CommandInfo $commandInfo, array &$actions) {
        $actions['Edit bundle'] = function() use($commandInfo) { $this->doQuestion($commandInfo); };
    }

    public function doQuestion(CommandInfo $commandInfo)
    {
        if (!$commandInfo->getGeneratorConfig()->askBundle()) {
            return;
        }
        $bundle = $commandInfo->getIo()->ask(
            'Bundle (optional)',
            $commandInfo->getMetaEntity()->getBundle() ?: $this->extractBundleFromArgument($commandInfo)
        );
        $commandInfo->getMetaEntity()->setBundle($bundle);
    }

    protected function extractBundleFromArgument(CommandInfo $commandInfo)
    {
        return $this->extractFromArgument($commandInfo)[0];
    }
}