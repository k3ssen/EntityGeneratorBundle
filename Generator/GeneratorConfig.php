<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Generator;

use Symfony\Component\Console\Helper\QuestionHelper;

class GeneratorConfig extends QuestionHelper
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getTraitOptions(): array
    {
        return $this->config['traits'];
    }

    public function autoGenerateRepository(): bool
    {
        return $this->config['auto_generate_repository'];
    }

    public function useDefaultValidations(): bool
    {
        return $this->config['use_default_validations'];
    }

    public function askBundle(): bool
    {
        return $this->config['ask_bundle'];
    }

    public function askSubDir(): bool
    {
        return $this->config['ask_sub_dir'];
    }

    public function askDisplayField(): bool
    {
        return $this->config['ask_display_field'];
    }

    public function askId(): bool
    {
        return $this->config['ask_id'];
    }

    public function askUnique(): bool
    {
        return $this->config['ask_unique'];
    }

    public function askNullable(): bool
    {
        return $this->config['ask_nullable'];
    }

    public function askLength(): bool
    {
        return $this->config['ask_length'];
    }

    public function askPrecision(): bool
    {
        return $this->config['ask_precision'];
    }

    public function askScale(): bool
    {
        return $this->config['ask_scale'];
    }

    public function askTargetEntity(): bool
    {
        return $this->config['ask_target_entity'];
    }

    public function askInversedBy(): bool
    {
        return $this->config['ask_inversed_by'];
    }

    public function askMappedBy(): bool
    {
        return $this->config['ask_mapped_by'];
    }

    public function askValidations(): bool
    {
        return $this->config['ask_validations'];
    }

    public function showAllValidationOptions(): bool
    {
        return $this->config['show_all_validation_options'];
    }
}