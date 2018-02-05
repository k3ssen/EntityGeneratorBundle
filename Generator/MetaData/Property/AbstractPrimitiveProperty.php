<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

use Doctrine\Common\Inflector\Inflector;

abstract class AbstractPrimitiveProperty extends AbstractProperty
{
    /** @var mixed */
    protected $default;

    public function isId(): ?bool
    {
        return $this->getAttribute('id');
    }

    public function setId(bool $id)
    {
        return $this->setAttribute('id', $id);
    }

    public function getDefault()
    {
        return $this->getAttribute('default');
    }

    /**
     * @param $default
     * @return static
     */
    public function setDefault($default)
    {
        return $this->setAttribute('default', $default);
    }

    public function getAnnotationLines(): array
    {
        $annotationLines = [
            '@ORM\Column('.$this->getColumnAnnotationOptions().')'
        ];
        if ($this->isId()) {
            $annotationLines[] = '@ORM\Id';
        }
        return array_merge($annotationLines, parent::getAnnotationLines());
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