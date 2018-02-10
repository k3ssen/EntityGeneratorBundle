<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;

class SubDirQuestion implements EntityQuestionInterface
{
    use NameExtractionTrait;

    public function addActions(CommandInfo $commandInfo, array &$actions) {
        $actions['Edit sub directory'] = function() use($commandInfo) { $this->doQuestion($commandInfo); };
    }

    public function doQuestion(CommandInfo $commandInfo)
    {
        $subDir = $commandInfo->getIo()->ask(
            'Sub directory (optional)',
            $commandInfo->getMetaEntity()->getSubDir() ?: $this->extractSubDirFromArgument($commandInfo)
        );
        $commandInfo->getMetaEntity()->setSubDir($subDir);
    }

    protected function extractSubDirFromArgument(CommandInfo $commandInfo)
    {
        return $this->extractFromArgument($commandInfo)[1];
    }
}