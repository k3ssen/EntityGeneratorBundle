<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData\Property;

use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaAttribute;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaValidation;

interface MetaPropertyInterface
{
    public function __construct(MetaEntity $metaEntity, ArrayCollection $metaAttributes, string $name);

    public static function getReturnType(): string;

    public static function getOrmType();

    public static function getOrmTypeAlias();

    public function getMetaEntity(): MetaEntity;

    public function setMetaEntity(MetaEntity $metaEntity);

    public function getName(): ?string;

    public function setName(string $name);

    public function isNullable(): ?bool;

    public function setNullable(?bool $nullable);

    public function isUnique(): ?bool;

    public function setUnique(?bool $unique);

    /** @return ArrayCollection|MetaValidation[] */
    public function getValidations(): ArrayCollection;

    public function setValidations(ArrayCollection $validations);

    public function addValidation(MetaValidation $validation);

    public function removeValidation(MetaValidation $validation);

    public function isHasValidation(): bool;

    /** @return array|ArrayCollection|MetaAttribute[] */
    public function getMetaAttributes(): ArrayCollection;

    public function addMetaAttribute(MetaAttribute $metaAttribute);

    public function removeMetaAttribute(MetaAttribute $metaAttribute);

    public function getMetaAttribute($name): MetaAttribute;

    public function hasAttribute($name): bool;

    public function getAttribute($name);

    public function setAttribute($name, $value);

    public function getAnnotationLines(): array;

    public function __toString();
}
