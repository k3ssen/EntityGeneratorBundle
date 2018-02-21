<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\MetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\RelationMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\BooleanMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\DateMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\DateTimeMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\IntegerMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\JsonMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\ManyToManyMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\OneToManyMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\SimpleArrayMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\StringMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\TextMetaProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\TimeMetaProperty;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class MetaValidationFactory
{
    public function createMetaValidation(MetaPropertyInterface $metaProperty, string $className, array $options = []): MetaValidation
    {
        if (strpos('\\', $className) === false) {
            $className = $this->getConstraintFullClassName($className);
        }
        return new MetaValidation($metaProperty, $className, $options);
    }

    protected function getConstraintFullClassName($shortName): string
    {
        return 'Symfony\\Component\\Validator\\Constraints\\'.$shortName;
    }

    public function getConstraintOptions(MetaPropertyInterface $metaProperty = null)
    {
        $constraints = [];
        $constraintsDir = dirname ((new \ReflectionClass(Constraints\NotNull::class))->getFileName());
        foreach (scandir($constraintsDir) as $fileName) {
            $className = basename($fileName, '.php');
            $classFullName = $this->getConstraintFullClassName($className);
            if (class_exists($classFullName) && is_a($classFullName, Constraint::class, true)) {
                $constraints[$classFullName] = $className;
            }
        }

        foreach ($this->getBlackListConstraints($metaProperty) as $blacklistConstraint) {
            unset($constraints[$blacklistConstraint]);
        }

        return $constraints;
    }

    protected function getBlackListConstraints(MetaPropertyInterface $metaProperty = null)
    {
        $blackList = [
            Constraints\AbstractComparison::class,  //This isn't an actual constaint, since it's abstractr
            //Constraints composed of other constraints are just too complex to be used in a generator like this.
            Constraints\Composite::class,
            Constraints\All::class,
            Constraints\Callback::class,
            Constraints\Existence::class,
            Constraints\Optional::class,
            Constraints\Collection::class,
            //What does traverse even do?
            Constraints\Traverse::class,
        ];
        if (!$metaProperty instanceof StringMetaProperty) {
            $blackList[] = Constraints\Bic::class;
            $blackList[] = Constraints\Currency::class;
            $blackList[] = Constraints\Iban::class;
            $blackList[] = Constraints\Image::class;
            $blackList[] = Constraints\Locale::class;
            $blackList[] = Constraints\Country::class;
            $blackList[] = Constraints\Ip::class;
            $blackList[] = Constraints\Uuid::class;
            //File and image actually aren't suitable for any orm-type, but one might use a string to setup a file/image property
            $blackList[] = Constraints\File::class;
            $blackList[] = Constraints\Image::class;
        }
        if (!$metaProperty instanceof StringMetaProperty && !$metaProperty instanceof TextMetaProperty) {
            $blackList[] = Constraints\NotBlank::class;
            $blackList[] = Constraints\Regex::class;
            $blackList[] = Constraints\Url::class;
            $blackList[] = Constraints\Email::class;
        }

        if (!$metaProperty instanceof StringMetaProperty && !$metaProperty instanceof IntegerMetaProperty) {
            //TODO: Not sure if these constraint would validate with only string or could work with integers as well
            $blackList[] = Constraints\CardScheme::class;
            $blackList[] = Constraints\Luhn::class;
            $blackList[] = Constraints\Isbn::class;
            $blackList[] = Constraints\Issn::class;
        }

        if (!$metaProperty instanceof IntegerMetaProperty
            && !$metaProperty instanceof DateTimeMetaProperty
            && !$metaProperty instanceof TimeMetaProperty
            && !$metaProperty instanceof DateMetaProperty
            //TODO: not sure if range would work with string if decimal is used.
        ) {
            $blackList[] = Constraints\Range::class;
        }

        if (!$metaProperty instanceof DateTimeMetaProperty) {
            $blackList[] = Constraints\DateTime::class;
        }
        if (!$metaProperty instanceof TimeMetaProperty) {
            $blackList[] = Constraints\Time::class;
        }
        if (!$metaProperty instanceof DateMetaProperty) {
            $blackList[] = Constraints\Date::class;
        }

        if (!$metaProperty instanceof RelationMetaPropertyInterface) {
            $blackList[] = Constraints\Valid::class;
        }
        if (!$metaProperty instanceof BooleanMetaProperty) {
            $blackList[] = Constraints\IsTrue::class;
            $blackList[] = Constraints\IsFalse::class;
        }

        if (!$metaProperty instanceof ManyToManyMetaProperty
            && !$metaProperty instanceof OneToManyMetaProperty
            && !$metaProperty instanceof SimpleArrayMetaProperty
            && !$metaProperty instanceof JsonMetaProperty //TODO: Not sure if json can be used as collection
        ) {
            $blackList[] = Constraints\Count::class;
        }

        return $blackList;
    }
}