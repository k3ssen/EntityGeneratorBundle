<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\EntityQuestion;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\MetaData\BundleProvider;
use Symfony\Component\Console\Question\Question;

class BundleQuestion implements EntityQuestionInterface
{
    public const PRIORITY = 80;

    /** @var BundleProvider */
    protected $bundleProvider;

    public function __construct(BundleProvider $bundleProvider)
    {
        $this->bundleProvider = $bundleProvider;
    }

    public function addActions(CommandInfo $commandInfo, array &$actions) {
        $actions['Edit bundle'] = function() use($commandInfo) {
            $this->doQuestion($commandInfo);
        };
    }

    public function doQuestion(CommandInfo $commandInfo)
    {
        $options = $this->bundleProvider->getBundleNameOptions();
        $commandInfo->getIo()->listing($options);
        $question = new Question('Bundle (optional)', $commandInfo->getMetaEntity()->getBundleName());
        $question->setAutocompleterValues($options);
        $bundleChoice = $commandInfo->getIo()->askQuestion($question);

        $bundleNamespace = $bundleChoice ? $this->bundleProvider->getBundleNamespaceByName($bundleChoice) : null;

        $commandInfo->getMetaEntity()->setBundleNamespace($bundleNamespace);
    }
}