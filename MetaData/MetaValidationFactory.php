<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\MetaData;

use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\AbstractRelationshipProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\BooleanProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\DateProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\DateTimeProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\IntegerProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\JsonProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\ManyToManyProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\OneToManyProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\SimpleArrayProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\StringProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\TextProperty;
use Kevin3ssen\EntityGeneratorBundle\MetaData\Property\TimeProperty;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class MetaValidationFactory
{
    public function createMetaValidation(AbstractProperty $metaProperty, string $className, array $options = []): MetaValidation
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

    public function getConstraintOptions(AbstractProperty $metaProperty = null)
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

    protected function getBlackListConstraints(AbstractProperty $metaProperty = null)
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
        if (!$metaProperty instanceof StringProperty) {
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
        if (!$metaProperty instanceof StringProperty && !$metaProperty instanceof TextProperty) {
            $blackList[] = Constraints\NotBlank::class;
            $blackList[] = Constraints\Regex::class;
            $blackList[] = Constraints\Url::class;
            $blackList[] = Constraints\Email::class;
        }

        if (!$metaProperty instanceof StringProperty && !$metaProperty instanceof IntegerProperty) {
            //TODO: Not sure if these constraint would validate with only string or could work with integers as well
            $blackList[] = Constraints\CardScheme::class;
            $blackList[] = Constraints\Luhn::class;
            $blackList[] = Constraints\Isbn::class;
            $blackList[] = Constraints\Issn::class;
        }

        if (!$metaProperty instanceof IntegerProperty
            && !$metaProperty instanceof DateTimeProperty
            && !$metaProperty instanceof TimeProperty
            && !$metaProperty instanceof DateProperty
            //TODO: not sure if range would work with string if decimal is used.
        ) {
            $blackList[] = Constraints\Range::class;
        }

        if (!$metaProperty instanceof DateTimeProperty) {
            $blackList[] = Constraints\DateTime::class;
        }
        if (!$metaProperty instanceof TimeProperty) {
            $blackList[] = Constraints\Time::class;
        }
        if (!$metaProperty instanceof DateProperty) {
            $blackList[] = Constraints\Date::class;
        }

        if (!$metaProperty instanceof AbstractRelationshipProperty) {
            $blackList[] = Constraints\Valid::class;
        }
        if (!$metaProperty instanceof BooleanProperty) {
            $blackList[] = Constraints\IsTrue::class;
            $blackList[] = Constraints\IsFalse::class;
        }

        if (!$metaProperty instanceof ManyToManyProperty
            && !$metaProperty instanceof OneToManyProperty
            && !$metaProperty instanceof SimpleArrayProperty
            && !$metaProperty instanceof JsonProperty //TODO: Not sure if json can be used as collection
        ) {
            $blackList[] = Constraints\Count::class;
        }

        return $blackList;
    }
}