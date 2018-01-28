<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property;

interface HasLengthInterface
{
    public function getLength(): ?int;

    public function setLength(?int $length);
}