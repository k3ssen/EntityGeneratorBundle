<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\PropertyQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractProperty;

interface PropertyQuestionInterface
{
    public function doQuestion(CommandInfo $commandInfo, AbstractProperty $metaProperty);
}