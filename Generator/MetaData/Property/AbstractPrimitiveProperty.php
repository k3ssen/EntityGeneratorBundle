<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\Common\Inflector\Inflector;

abstract class AbstractPrimitiveProperty extends AbstractProperty
{
    /** @var bool */
    protected $id = false;

    /** @var mixed */
    protected $default;

    public function isId(): ?bool
    {
        return $this->id;
    }

    public function setId(bool $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param $default
     * @return static
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function getAnnotationLines(): array
    {
        $annotationLines = [
            '@ORM\Column('.$this->getColumnAnnotationOptions().')'
        ];
        if ($this->isId()) {
            $annotationLines[] = '@ORM\Id';
        }
        return $annotationLines;
    }

    public function getColumnAnnotationOptions()
    {
        $optionsString = 'name="'.Inflector::tableize($this->getName()).'"';
        $optionsString .= ', type="'.$this->getOrmType().'"';
        $optionsString .= $this->isUnique() ? ', unique=true' : '';
        $optionsString .= $this->isNullable() ? ', nullable=true' : '';

        return $optionsString;
    }
}