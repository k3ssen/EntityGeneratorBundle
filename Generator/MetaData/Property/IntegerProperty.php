<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class IntegerProperty extends AbstractPrimitiveProperty implements HasLengthInterface
{
    /** @var int */
    protected $length;

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length)
    {
        $this->length = $length;
    }

    public function getReturnType(): string
    {
        return 'int';
    }

    public function getColumnAnnotationOptions()
    {
        $optionsString = parent::getColumnAnnotationOptions();
        $optionsString .= $this->getLength() ? ', length='.$this->getLength() : '';

        return $optionsString;
    }

    public function getOrmType(): string
    {
        return Type::INTEGER;
    }
}
