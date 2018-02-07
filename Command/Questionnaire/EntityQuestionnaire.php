<?php
declare(strict_types=1);

namespace Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire;

use Kevin3ssen\EntityGeneratorBundle\Command\Helper\CommandInfo;
use Kevin3ssen\EntityGeneratorBundle\Command\Questionnaire\EntityQuestion\EntityQuestionInterface;
use Kevin3ssen\EntityGeneratorBundle\Generator\MetaData\MetaEntity;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class EntityQuestionnaire
{
    /** @var iterable|EntityQuestionInterface[] */
    protected $entityQuestions;

    public function __construct(iterable $entityQuestions)
    {
        $this->entityQuestions = $entityQuestions;
    }

    public function makeEntity(CommandInfo $commandInfo): ?MetaEntity
    {
        $actions = [];
        foreach ($this->entityQuestions as $entityQuestion) {
            if (!$entityQuestion instanceof EntityQuestionInterface) {
                throw new LogicException(sprintf('Service "%s" is used as entityQuestion, but does not implement %s', get_class($entityQuestion), EntityQuestionInterface::class));
            }
            $entityQuestion->addActions($commandInfo, $actions);
            $entityQuestion->doQuestion($commandInfo);
        }
        $actions['All done! Generate entity!'] = null;
        do {
            $chosenAction = $commandInfo->getIo()->choice('Next action', array_keys($actions));
            $nextAction = $actions[$chosenAction] ?? null;
            if ($nextAction) {
                $nextAction();
            }
        } while ($nextAction);

        return $commandInfo->getMetaEntity();
    }
}