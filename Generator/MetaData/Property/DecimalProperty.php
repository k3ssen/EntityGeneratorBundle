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
        return $this->precision;
    }

    public function setPrecision(int $precision): self
    {
        $this->precision = $precision;
        return $this;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function setScale(int $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    public function getReturnType(): string
    {
        return 'string';
    }

    protected function getColumnAnnotationOptions()
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
