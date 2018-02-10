<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\PropertyQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaValidation;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaValidationFactory;
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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class ValidationsQuestion implements PropertyQuestionInterface
{
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

    public function doQuestion(CommandInfo $commandInfo, AbstractProperty $metaProperty)
    {
        $commandInfo->saveTemporaryFile();
        $actionChoices = [
            static::ACTION_VALIDATION_STOP => null,
            static::ACTION_VALIDATION_ADD => 'Add new validation',
            static::ACTION_VALIDATION_EDIT => 'Edit validation',
            static::ACTION_VALIDATION_REMOVE => 'Remove validation',
        ];
        if (!$metaProperty->getValidations()->count()) {
            unset($actionChoices[static::ACTION_VALIDATION_EDIT], $actionChoices[static::ACTION_VALIDATION_REMOVE]);
        }
        $nextAction = $commandInfo->getIo()->choice('Validations (press <comment>[enter]</comment> to stop)' , $actionChoices);
        $nextAction = array_search($nextAction, $actionChoices);

        if ($nextAction === static::ACTION_VALIDATION_STOP) {
            return;
        }
        if ($nextAction === static::ACTION_VALIDATION_REMOVE) {
            $metaValidation = $this->askMetaPropertyValidationChoice($commandInfo, $metaProperty);
            $metaProperty->removeValidation($metaValidation);
            unset($metaValidation);
            $this->doQuestion($commandInfo, $metaProperty);
            return;
        }
        if ($nextAction === static::ACTION_VALIDATION_EDIT) {
            $metaValidation = $this->askMetaPropertyValidationChoice($commandInfo, $metaProperty);
            $validationOptions = get_class_vars($metaValidation->getClassName());
        } else {
            $validationClass = $this->askValidationChoice($commandInfo, $metaProperty);
            if (!$validationClass) {
                $this->doQuestion($commandInfo, $metaProperty);
            }
            $validationOptions = get_class_vars($validationClass);
            $requiredOptions = $this->getValidationRequiredOptions($validationClass);

            $customValidationOptions = [];
            foreach ($requiredOptions as $requiredOption) {
                if (!array_key_exists($requiredOption, $validationOptions)) {
                    throw new \RuntimeException(sprintf('Something unexpected went wrong: the required option %s does not exist in validationOptions: %s', $requiredOption, implode(',', array_keys($validationOptions))));
                }
                $customValidationOptions[$requiredOption] = $this->askValidationOptionValue($commandInfo, $requiredOption, $validationOptions[$requiredOption]);
            }
            $metaValidation = $this->metaValidationFactory->createMetaValidation($metaProperty, $validationClass, $customValidationOptions);
            $commandInfo->saveTemporaryFile();

            //Unset the options that aren't required, to prevent being bothered with unnecessary questions
            foreach ($validationOptions as $key => $validationOption) {
                if (!array_key_exists($key, $requiredOptions)) {
                    unset($validationOptions[$key]);
                }
            }
        }
        if (count($validationOptions)) {
            do {
                $editValidationOptionChoice = $commandInfo->getIo()->choice('Choose option to edit  (press <comment>[enter]</comment> to stop)', array_merge([null], array_keys($validationOptions)));
                if ($editValidationOptionChoice) {
                    $defaultValidationOptionValue = $metaValidation->getOptions()[$editValidationOptionChoice] ?? $validationOptions[$editValidationOptionChoice];
                    $customValidationOptions[$editValidationOptionChoice] = $this->askValidationOptionValue($commandInfo, $editValidationOptionChoice, $defaultValidationOptionValue);
                    $metaValidation->setOptions($customValidationOptions);
                    $commandInfo->saveTemporaryFile();
                }
            } while ($editValidationOptionChoice);
        }
        $this->doQuestion($commandInfo, $metaProperty);
    }

    protected function askMetaPropertyValidationChoice(CommandInfo $commandInfo, AbstractProperty $metaProperty): MetaValidation
    {
        $validations = $metaProperty->getValidations();
        $validationChoice = $commandInfo->getIo()->choice('Edit validation', $validations->toArray());
        foreach ($validations as $validation) {
            if ($validation->getClassName() === $validationChoice) {
                return $validation;
            }
        }
        throw new \RuntimeException(sprintf('No property found for choice %s', $validationChoice));
    }

    protected function askValidationOptionValue(CommandInfo $commandInfo, $validationOption, $defaultValue)
    {
        return $commandInfo->getIo()->ask($validationOption, $defaultValue, function ($value) {
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
        } catch(ConstraintDefinitionException $exception) {
            $requiredOptions = ['value'];
        }
        return $requiredOptions;
    }

    protected function askValidationChoice(CommandInfo $commandInfo, AbstractProperty $metaProperty = null)
    {
        $options = $this->getConstraintOptions($metaProperty);
        $commandInfo->getIo()->listing($options);
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
        $validationChoice = $commandInfo->getIo()->askQuestion($question);
        return array_search($validationChoice, $options);
    }

    protected function getConstraintOptions(AbstractProperty $metaProperty = null)
    {
        $constraints = [];
        $constraintsDir = dirname ((new \ReflectionClass(Constraints\NotNull::class))->getFileName());
        foreach (scandir($constraintsDir) as $fileName) {
            $className = basename($fileName, '.php');
            $classFullName = 'Symfony\\Component\\Validator\\Constraints\\'.$className;
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