<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\Command\EntityQuestion;

use K3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;

interface EntityQuestionInterface
{
    public function addActions(CommandInfo $commandInfo, array &$actions);
    public function doQuestion(CommandInfo $commandInfo);
}