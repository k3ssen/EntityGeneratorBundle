<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\AttributeQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttributeInterface;

interface AttributeQuestionInterface
{
    public function doQuestion(CommandInfo $commandInfo, MetaAttributeInterface $metaAttribute);

    public function addAttribute(string $attributeName, array $attributeInfo = []);

    public function supportsAttribute(string $attributeName): bool;
}