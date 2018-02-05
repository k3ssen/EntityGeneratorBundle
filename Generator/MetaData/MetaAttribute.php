<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;

class MetaAttribute
{
    protected const TYPE_STRING = 'string';
    protected const TYPE_INT = 'int';
    protected const TYPE_BOOL = 'bool';
    protected const TYPE_ARRAY = 'array';

    protected const ALLOWED_TYPES = [
        self::TYPE_STRING,
        self::TYPE_INT,
        self::TYPE_BOOL,
        self::TYPE_ARRAY,
    ];

    /** @var AbstractProperty */
    protected $metaProperty;

    /** @var string */
    protected $name;

    /** @var bool */
    protected $nullable = true;

    /** @var string */
    protected $type = 'string';

    //(optional) question to be displayed in the interface (name will be used if no question is set)
    protected $question;

    //(optional) value that will be used if no value has been set yet.
    protected $defaultValue;

    //The actual value that will be used, unless it was never set (in which case we use defaultValue)
    protected $value;

    //(optional) languageExpression to determine if this attribute should be asked or not
    protected $condition;

    //(optional) languageExpression for additional validations (other than type or nullable)
    protected $validation;

    protected $questionService;

    //Helps us know that the value has been set and that we should not use the default value
    protected $valueIsSet = false;

    //Helps us know that the user has set this value (which can be used to determine if a(nother) question should be asked or not)
    protected $valueIsSetByUserInput = false;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->setQuestion(ucfirst($name));
    }

    public function getMetaProperty(): ?AbstractProperty
    {
        return $this->metaProperty;
    }

    public function setMetaProperty(AbstractProperty $metaProperty): self
    {
        $this->metaProperty = $metaProperty;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function isBool(): bool
    {
        return $this->getType() === static::TYPE_BOOL;
    }

    public function isInt(): bool
    {
        return $this->getType() === static::TYPE_INT;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, static::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Type "%s" is not allowed; The following types are allowed: %s',
                $type,
                implode(', ', static::ALLOWED_TYPES)
            ));
        }
        $this->type = $type;
        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    public function getValidation()
    {
        return $this->validation;
    }

    public function setValidation($validation)
    {
        $this->validation = $validation;
        return $this;
    }

    public function getValue()
    {
        if ($this->getValueIsSet()) {
            return $this->value;
        }
        return $this->getDefaultValue();
    }

    public function setValue($value)
    {
        if ($this->isNullable() && in_array(strtolower((string) $value), ['null', '~'])) {
            $this->value = null;
            return $this;
        }
        switch (strtolower($this->getType())) {
            case static::TYPE_BOOL:
                if (in_array(strtolower((string) $value), ['false', 'f', 'no', 'n'])) {
                    $value = false;
                } elseif (in_array(strtolower((string) $value), ['true', 't', 'yes', 'y'])) {
                    $value = true;
                }
                $value = (bool) $value;
                break;
            case static::TYPE_INT:
                $value = (int) $value;
                break;
            case static::TYPE_ARRAY:
                $value = str_replace([' ,',' , ',', '], ',', (string) $value);
                $value = explode(',', $value);
                break;
            default:
                $value = (string) $value;
        }
        $this->value = $value;
        $this->setValueIsSet(true);
        return $this;
    }

    public function getQuestionService(): ?string
    {
        return $this->questionService;
    }

    public function setQuestionService(?string $questionService)
    {
        $this->questionService = $questionService;
        return $this;
    }

    public function getValueIsSet(): ?bool
    {
        return $this->valueIsSet;
    }

    public function setValueIsSet(bool $valueIsSet = true)
    {
        $this->valueIsSet = $valueIsSet;
        return $this;
    }

    public function getValueIsSetByUserInput(): ?bool
    {
        return $this->valueIsSetByUserInput;
    }

    public function setValueIsSetByUserInput(bool $valueIsSetByUserInput = true)
    {
        $this->valueIsSetByUserInput = $valueIsSetByUserInput;
        return $this;
    }
}
