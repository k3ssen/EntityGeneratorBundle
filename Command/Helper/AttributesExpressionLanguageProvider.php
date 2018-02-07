<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class AttributesExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [];
    }
}