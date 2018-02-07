<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\AttributeQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaAttribute;

interface AttributeQuestionInterface
{
    public function doQuestion(CommandInfo $commandInfo, MetaAttribute $metaAttribute);
}