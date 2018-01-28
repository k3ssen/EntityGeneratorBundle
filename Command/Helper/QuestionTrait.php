<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractPrimitiveProperty;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\Property\AbstractRelationshipProperty;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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

    protected function showCurrentOverview()
    {
        $this->getIo()->block(
            sprintf('Currently working on Entity "%s"', $this->commandInfo->metaEntity->getName()),
            'INFO',
            'fg=black;bg=cyan',
            ' ',
            true
        );
        $propertyOutputs = [];
        foreach ($this->commandInfo->metaEntity->getProperties() as $property) {
            $propertyOutputs[] = [
                $property->getName(),
                $property->getOrmType()
                    . ($property instanceof AbstractRelationshipProperty ? '<comment> ['.$property->getTargetEntity().']</comment>' : '')
                    . ($property instanceof AbstractPrimitiveProperty && $property->isId() ? ' [<comment>id</comment>]' : '')
                    . ($this->commandInfo->metaEntity->getDisplayProperty() === $property ? ' [<comment>display field</comment>]' : '')
                    . ($property instanceof AbstractPrimitiveProperty && $property->isNullable() ? ' <comment>[nullable]</comment>' : '')
            ];
        }
        $this->getIo()->table(['Property', 'Type'], $propertyOutputs);
    }

    protected function askQuestion(Question $question)
    {
        $this->saveTemporaryFile();
        return $this->ask($this->commandInfo->input, $this->commandInfo->output, $question);
    }

    protected function askSimpleQuestion(string $question, $defaultValue)
    {
        $question = '<info>'.$question.'</info>';
        if (is_bool($defaultValue)) {
            $question .= ' [<comment>'.($defaultValue ? 'Y/n' : 'y/N').'</comment>]: ';
            return $this->askQuestion(new ConfirmationQuestion($question, $defaultValue));
        } else {
            $question .= $defaultValue ? ' [<comment>' . $defaultValue . '</comment>]: ' : ': ';
            return $this->askQuestion(new Question($question, $defaultValue));
        }
    }

    protected function outputOptions(array $options)
    {
        if (count($options)) {
            $this->commandInfo->output->writeln('Available options: ');
            $this->commandInfo->output->write(sprintf('<info>%s</info> ', implode(', ', $options)));
            $this->commandInfo->output->writeln('');
        }
    }

    protected function saveTemporaryFile()
    {
        $serializedMetaData = serialize($this->commandInfo->metaEntity);
        $temp = sys_get_temp_dir(). '/last_metadata';
        file_put_contents($temp, $serializedMetaData);
    }
}