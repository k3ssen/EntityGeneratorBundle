<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\MetaPropertyInterface;

interface MetaValidationInterface
{
    public function __construct(MetaPropertyInterface $metaProperty, string $className, array $options = []);

    public function getClassName(): ?string;

    public function setClassName(string $className);

    public function getClassShortName(): ?string;

    public function getOptions(): ?array;

    public function setOptions(array $options);

    public function getAnnotationFormatted(): string;

    public function getMetaProperty(): ?MetaPropertyInterface;

    public function setMetaProperty(MetaPropertyInterface $metaProperty);

    public function __toString();
}
