<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttributeInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

trait EvaluationTrait
{
    protected function evaluateMetaAttribute(MetaAttributeInterface $metaAttribute, string $expression)
    {
        return $this->getExpressionLanguage()->evaluate($expression, [
            'this' => $metaAttribute,
            'metaProperty' => $metaAttribute->getMetaProperty(),
            'metaEntity' => $metaAttribute->getMetaProperty()->getMetaEntity(),
        ]);
    }

    protected function getExpressionLanguage(): ExpressionLanguage
    {
        if (!isset($this->expressionLanguage)) {
            $this->expressionLanguage = new ExpressionLanguage();
        }
        return $this->expressionLanguage;
    }
}
