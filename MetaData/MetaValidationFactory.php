<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\BooleanMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\DateMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\DateTimeMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\IntegerMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\JsonMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\ManyToManyMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\MetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\OneToManyMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\RelationMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\SimpleArrayMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\StringMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\TextMetaPropertyInterface;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\TimeMetaPropertyInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class MetaValidationFactory
{
    /** @var string */
    protected $metaValidationClass;

    public function setMetaValidationClass(string $class)
    {
        $this->metaValidationClass = $class;
    }

    public function createMetaValidation(MetaPropertyInterface $metaProperty, string $className, array $options = []): MetaValidationInterface
    {
        if (strpos('\\', $className) === false) {
            $className = $this->getConstraintFullClassName($className);
        }
        return new $this->metaValidationClass($metaProperty, $className, $options);
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
        if (!$metaProperty instanceof StringMetaPropertyInterface) {
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
        if (!$metaProperty instanceof StringMetaPropertyInterface && !$metaProperty instanceof TextMetaPropertyInterface) {
            $blackList[] = Constraints\NotBlank::class;
            $blackList[] = Constraints\Regex::class;
            $blackList[] = Constraints\Url::class;
            $blackList[] = Constraints\Email::class;
        }

        if (!$metaProperty instanceof StringMetaPropertyInterface && !$metaProperty instanceof IntegerMetaPropertyInterface) {
            //TODO: Not sure if these constraint would validate with only string or could work with integers as well
            $blackList[] = Constraints\CardScheme::class;
            $blackList[] = Constraints\Luhn::class;
            $blackList[] = Constraints\Isbn::class;
            $blackList[] = Constraints\Issn::class;
        }

        if (!$metaProperty instanceof IntegerMetaPropertyInterface
            && !$metaProperty instanceof DateTimeMetaPropertyInterface
            && !$metaProperty instanceof TimeMetaPropertyInterface
            && !$metaProperty instanceof DateMetaPropertyInterface
            //TODO: not sure if range would work with string if decimal is used.
        ) {
            $blackList[] = Constraints\Range::class;
        }

        if (!$metaProperty instanceof DateTimeMetaPropertyInterface) {
            $blackList[] = Constraints\DateTime::class;
        }
        if (!$metaProperty instanceof TimeMetaPropertyInterface) {
            $blackList[] = Constraints\Time::class;
        }
        if (!$metaProperty instanceof DateMetaPropertyInterface) {
            $blackList[] = Constraints\Date::class;
        }

        if (!$metaProperty instanceof RelationMetaPropertyInterface) {
            $blackList[] = Constraints\Valid::class;
        }
        if (!$metaProperty instanceof BooleanMetaPropertyInterface) {
            $blackList[] = Constraints\IsTrue::class;
            $blackList[] = Constraints\IsFalse::class;
        }

        if (!$metaProperty instanceof ManyToManyMetaPropertyInterface
            && !$metaProperty instanceof OneToManyMetaPropertyInterface
            && !$metaProperty instanceof SimpleArrayMetaPropertyInterface
            && !$metaProperty instanceof JsonMetaPropertyInterface //TODO: Not sure if json can be used as collection
        ) {
            $blackList[] = Constraints\Count::class;
        }

        return $blackList;
    }
}