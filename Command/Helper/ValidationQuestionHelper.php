<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaValidation;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaValidationFactory;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractRelationshipProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\BooleanProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\DateProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\DateTimeProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\IntegerProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\JsonProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\ManyToManyProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\OneToManyProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\SimpleArrayProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\StringProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\TextProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\TimeProperty;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class ValidationQuestionHelper
{
    use QuestionTrait;

    protected const ACTION_VALIDATION_STOP = 0;
    protected const ACTION_VALIDATION_ADD = 1;
    protected const ACTION_VALIDATION_EDIT = 2;
    protected const ACTION_VALIDATION_REMOVE = 3;

    /** @var MetaValidationFactory */
    protected $metaValidationFactory;

    public function __construct(MetaValidationFactory $metaValidationFactory)
    {
        $this->metaValidationFactory = $metaValidationFactory;
    }

    public function validationAction(CommandInfo $commandInfo, AbstractProperty $metaProperty)
    {
        $this->commandInfo = $commandInfo;

        if (!$commandInfo->generatorConfig->askValidations()) {
            return;
        }

        $actionChoices = [
            static::ACTION_VALIDATION_STOP => 'Stop managing validations for this property',
            static::ACTION_VALIDATION_ADD => 'Add new validation',
            static::ACTION_VALIDATION_EDIT => 'Edit validation',
            static::ACTION_VALIDATION_REMOVE => 'Remove validation',
        ];
        if (!$metaProperty->getValidations()->count()) {
            unset($actionChoices[static::ACTION_VALIDATION_EDIT], $actionChoices[static::ACTION_VALIDATION_REMOVE]);
        }
        $nextAction = $this->getIo()->choice('Validations' , $actionChoices);
        $nextAction = array_search($nextAction, $actionChoices);

        if ($nextAction === static::ACTION_VALIDATION_STOP) {
            return;
        }
        if ($nextAction === static::ACTION_VALIDATION_REMOVE) {
            $metaValidation = $this->askMetaPropertyValidationChoice($metaProperty);
            $metaValidation->getMetaProperty()->removeValidation($metaValidation);
            unset($metaValidation);
            $this->validationAction($commandInfo, $metaProperty);
            return;
        }
        if ($nextAction === static::ACTION_VALIDATION_EDIT) {
            $metaValidation = $this->askMetaPropertyValidationChoice($metaProperty);
            $validationOptions = get_class_vars($metaValidation->getClassName());
            $requiredOptions = $metaValidation->getOptions();
        } else {
            $validationClass = $this->askValidationChoice($metaProperty);
            if (!$validationClass) {
                $this->validationAction($commandInfo, $metaProperty);
            }
            $validationOptions = get_class_vars($validationClass);
            $requiredOptions = $this->getValidationRequiredOptions($validationClass);

            $customValidationOptions = [];
            foreach ($requiredOptions as $requiredOption) {
                if (!array_key_exists($requiredOption, $validationOptions)) {
                    throw new \RuntimeException(sprintf('Something unexpected went wrong: the required option %s does not exist in validationOptions: %s', $requiredOption, implode(',', array_keys($validationOptions))));
                }
                $customValidationOptions[$requiredOption] = $this->askValidationOptionValue($requiredOption, $validationOptions[$requiredOption]);
            }
            $metaValidation = $this->metaValidationFactory->createMetaValidation($metaProperty, $validationClass, $customValidationOptions);
            $this->saveTemporaryFile();
        }

        if (!$commandInfo->generatorConfig->showAllValidationOptions()) {
            foreach ($validationOptions as $key => $validationOption) {
                if (!array_key_exists($key, $requiredOptions)) {
                    unset($validationOptions[$key]);
                }
            }
        }
        if (count($validationOptions)) {
            do {
                $editValidationOptionChoice = $this->getIo()->choice('Choose option to edit (optional)', array_merge([null], array_keys($validationOptions)));
                if ($editValidationOptionChoice) {
                    $defaultValidationOptionValue = $metaValidation->getOptions()[$editValidationOptionChoice] ?? $validationOptions[$editValidationOptionChoice];
                    $customValidationOptions[$editValidationOptionChoice] = $this->askValidationOptionValue($editValidationOptionChoice, $defaultValidationOptionValue);
                    $metaValidation->setOptions($customValidationOptions);
                    $this->saveTemporaryFile();
                }
            } while ($editValidationOptionChoice);
        }
        $this->validationAction($commandInfo, $metaProperty);
    }

    protected function askMetaPropertyValidationChoice(AbstractProperty $metaProperty): MetaValidation
    {
        $validations = $metaProperty->getValidations();
        $validationChoice = $this->getIo()->choice('Edit validation', $validations->toArray());
        foreach ($validations as $validation) {
            if ($validation->getClassName() === $validationChoice) {
                return $validation;
            }
        }
        throw new \RuntimeException(sprintf('No property found for choice %s', $validationChoice));
    }

    protected function askValidationOptionValue($validationOption, $defaultValue)
    {
        return $this->ask($validationOption, $defaultValue, function ($value) {
            if (is_numeric($value)) {
                return (int) $value;
            }
            if (in_array($value, ['', '~', 'null', 'NULL'])) {
                return null;
            }
            if (in_array($value, ['true', 'TRUE'])) {
                return true;
            }
            if (in_array($value, ['false', 'FALSE'])) {
                return false;
            }
            return $value;
        });
    }

    protected function getValidationRequiredOptions(string $validationClass)
    {
        /** @var Constraint $validation */
        try {
            $validation = new $validationClass();
            $requiredOptions = $validation->getRequiredOptions();
        } catch (MissingOptionsException $exception) {
            $requiredOptions = $exception->getOptions();
        }
        return $requiredOptions;
    }

    protected function askValidationChoice(AbstractProperty $metaProperty = null)
    {
        $options = $this->getConstraintOptions($metaProperty);
        $this->outputOptions($options);
        $question = new Question('Add validation (optional)');
        $optionValues = array_values($options);
        $question->setAutocompleterValues(array_merge($optionValues, array_map('lcfirst', $optionValues), array_map('strtolower', $optionValues)));
        $question->setNormalizer(function ($choice) use ($options) {
            foreach ($options as $option) {
                if ($choice && strtolower($option) === strtolower($choice)) {
                    return $option;
                }
            }
            return $choice;
        });
        $validationChoice = $this->askQuestion($question);
        return array_search($validationChoice, $options);
    }

    protected function getConstraintOptions(AbstractProperty $metaProperty = null)
    {
        $constraints = [];
        $constraintsDir = dirname ((new \ReflectionClass(Constraints\NotNull::class))->getFileName());
        foreach (scandir($constraintsDir) as $fileName) {
            $className = basename($fileName, '.php');
            $classFullName = 'Symfony\\Component\\Validator\\Constraints\\'.$className;
            if (class_exists($classFullName) && is_subclass_of($classFullName, Constraint::class)) {
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