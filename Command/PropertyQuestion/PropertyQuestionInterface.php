<?php
declare(strict_types=1);

namespace K3ssen\EntityGeneratorBundle\Command\PropertyQuestion;

use K3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use K3ssen\EntityGeneratorBundle\MetaData\Property\MetaPropertyInterface;

interface PropertyQuestionInterface
{
    public function doQuestion(CommandInfo $commandInfo, MetaPropertyInterface $metaProperty);
}