<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class StringProperty extends AbstractPrimitiveProperty implements HasLengthInterface
{
    public function getLength(): ?int
    {
        return $this->getAttribute('length');
    }

    public function setLength(?int $length)
    {
        return $this->setAttribute('length', $length);
    }

    public function getReturnType(): string
    {
        return Type::STRING;
    }

    public function getColumnAnnotationOptions()
    {
        $optionsString = parent::getColumnAnnotationOptions();
        $optionsString .= $this->getLength() ? ', length='.$this->getLength() : '';

        return $optionsString;
    }

    public function getOrmType(): string
    {
        return Type::STRING;
    }
}
