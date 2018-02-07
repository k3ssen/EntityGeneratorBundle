<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;

interface EntityQuestionInterface
{
    public function addActions(CommandInfo $commandInfo, array &$actions);
    public function doQuestion(CommandInfo $commandInfo);
}