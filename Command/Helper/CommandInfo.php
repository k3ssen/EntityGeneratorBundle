<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Helper;

use Kevin3ssen\EntityGeneratorBundle\Command\Style\CommandStyle;
use Kevin3ssen\EntityGeneratorBundle\MetaData\MetaEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class CommandInfo
{
    /** @var InputInterface */
    protected $input;
    /** @var OutputInterface */
    protected $output;
    /** @var MetaEntity */
    protected $metaEntity;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        MetaEntity $metaEntity = null
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->metaEntity = $metaEntity;
    }

    public function getInput(): InputInterface
    {
        return $this->input;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function setMetaEntity(MetaEntity $metaEntity): self
    {
        $this->metaEntity = $metaEntity;
        return $this;
    }

    public function getMetaEntity(): MetaEntity
    {
        if ($this->metaEntity) {
            return $this->metaEntity;
        }
        throw new \RuntimeException(sprintf('No metaEntity set in %s; Make sure "setMetaEntity" is called before calling "getMetaEntity"', static::class));
    }

    public function getIo(): SymfonyStyle
    {
        if (!isset($this->io)) {
            $this->io = new CommandStyle($this->input, $this->output);
        }
        return $this->io;
    }

    public function saveTemporaryFile()
    {
        $serializedMetaData = serialize($this->getMetaEntity());
        $temp = sys_get_temp_dir(). '/last_metadata';
        file_put_contents($temp, $serializedMetaData);
    }

    public function loadMetaEntityFromTemporaryFile()
    {
        $temp = sys_get_temp_dir() . '/last_metadata';
        if (file_exists($temp)) {
            $metaData = file_get_contents($temp);
            $metaEntity = unserialize($metaData);
            $this->setMetaEntity($metaEntity);
        } else {
            throw new FileNotFoundException('No savepoint file found.');
        }
    }
}