<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\DBAL\Types\Type;

class DecimalProperty extends AbstractPrimitiveProperty
{
    /** @var int */
    protected $precision;

    /** @var int */
    protected $scale;

    public function getPrecision(): ?int
    {
        return $this->getAttribute('precision');
    }

    public function setPrecision(?int $precision): self
    {
        return $this->setAttribute('precision', $precision);
    }

    public function getScale(): ?int
    {
        return $this->getAttribute('scale');
    }

    public function setScale(?int $scale): self
    {
        return $this->setAttribute('scale', $scale);
    }

    public function getReturnType(): string
    {
        return 'string';
    }

    public function getColumnAnnotationOptions()
    {
        $optionsString = parent::getColumnAnnotationOptions();
        $optionsString .= $this->getPrecision() ? ', length='.$this->getPrecision() : '';
        $optionsString .= $this->getScale() ? ', scale='.$this->getScale() : '';

        return $optionsString;
    }

    public function getOrmType(): string
    {
        return Type::DECIMAL;
    }
}
