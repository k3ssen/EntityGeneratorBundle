<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractPrimitiveProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractRelationshipProperty;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

trait QuestionTrait
{
    /** @var CommandInfo */
    protected $commandInfo;

    protected function getIo(): SymfonyStyle
    {
        if (isset($this->io)) {
            return $this->io;
        }
        if (!$this->commandInfo) {
            throw new \RuntimeException('Cannot retrieve io before commandInfo has been set.');
        }
        return $this->io = new SymfonyStyle($this->commandInfo->input, $this->commandInfo->output);
    }

    protected function askNextAction()
    {
        $this->showCurrentOverview();
        $actionChoices = [
            1 => 'Add property',
            2 => 'Edit property',
            3 => 'Remove property',
            4 => 'Change entity name',
            5 => 'All done! Generate entity!',
            //Savepoint with name
        ];
        if (!$this->commandInfo->metaEntity->getProperties()->count()) {
            unset($actionChoices[2], $actionChoices[3]);
        }
    }

    protected function showCurrentOverview()
    {
        $this->getIo()->text(sprintf('<info>Current Entity:</info> %s', $this->commandInfo->metaEntity->getName()));
        $propertyOutputs = [];
        foreach ($this->commandInfo->metaEntity->getProperties() as $property) {
            $validationNames = [];
            foreach ($property->getValidations() as $validation) {
                $validationNames[] = $validation->getClassShortName();
            }
            $propertyOutputs[] = [
                $property->getName(),
                $property->getOrmType()
                    . ($property instanceof AbstractRelationshipProperty ? '<comment> ['.$property->getTargetEntity().']</comment>' : '')
                    . ($property instanceof AbstractPrimitiveProperty && $property->isId() ? ' [<comment>id</comment>]' : '')
                    . ($this->commandInfo->metaEntity->getDisplayProperty() === $property ? ' [<comment>display field</comment>]' : '')
                    . ($property instanceof AbstractPrimitiveProperty && $property->isNullable() ? ' <comment>[nullable]</comment>' : '')
                ,
                implode(', ', $validationNames),
            ];
        }
        $this->getIo()->table(['Property', 'Type', 'Validations'], $propertyOutputs);
    }

    protected function askQuestion(Question $question)
    {
        $this->saveTemporaryFile();
        $answer = $this->getIo()->askQuestion($question);
        return $answer === 'null' || $answer === '~' ? null : $answer;
    }

    protected function confirm($question, bool $default = true): bool
    {
        $this->saveTemporaryFile();
        return $this->getIo()->confirm($question, $default);
    }

    protected function ask($question, $default = null, callable $validator = null)
    {
        $this->saveTemporaryFile();
        $answer = $this->getIo()->ask($question, $default, $validator);
        return $answer === 'null' || $answer === '~' ? null : $answer;
    }

    protected function header($text)
    {
        $this->getIo()->block($text, null, 'options=bold;fg=cyan;bg=blue', '    ', true);
    }

    protected function outputOptions(array $options)
    {
        if (count($options)) {
            $this->commandInfo->output->writeln(sprintf(' <info>Available options:</info> <comment>%s</comment>', implode('</comment>, <comment>', $options)));
        }
    }

    protected function saveTemporaryFile()
    {
        $serializedMetaData = serialize($this->commandInfo->metaEntity);
        $temp = sys_get_temp_dir(). '/last_metadata';
        file_put_contents($temp, $serializedMetaData);
    }
}