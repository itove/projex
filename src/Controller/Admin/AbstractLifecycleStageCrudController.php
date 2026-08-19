<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\LifecycleStageInterface;
use App\Entity\Project;
use App\Exception\PreviousStageAttachmentsIncompleteException;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use App\Service\Lifecycle\StageAttachmentComplianceService;
use App\Service\OrgAccessService;
use App\Service\ProjectDisplayService;
use App\Service\ProjectTaskService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base for the seven lifecycle stage CRUD controllers. Adds a stage-scoped
 * task list and attachment checklist to the detail page.
 */
abstract class AbstractLifecycleStageCrudController extends AbstractOrgScopedLifecycleCrudController
{
    public function __construct(
        OrgAccessService $orgAccessService,
        protected readonly ProjectTaskService $projectTaskService,
        protected readonly ProjectLifecycleStageRegistry $stageRegistry,
        protected readonly StageAttachmentComplianceService $attachmentComplianceService,
        protected readonly ProjectDisplayService $projectDisplayService,
        protected readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($orgAccessService);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/index', 'admin/lifecycle_stage/index.html.twig')
            ->overrideTemplate('crud/detail', 'admin/lifecycle_stage/detail.html.twig');
    }

    public function new(AdminContext $context): KeyValueStore|Response
    {
        $blocked = $this->redirectIfPreviousStageIncomplete($context);
        if ($blocked !== null) {
            return $blocked;
        }

        try {
            return parent::new($context);
        } catch (PreviousStageAttachmentsIncompleteException $exception) {
            $this->addFlash('danger', $exception->getMessage());

            return $this->redirectToProjectDetail($exception->getProject());
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof LifecycleStageInterface && $entityInstance->getId() === null) {
            $project = $entityInstance->getProject();
            if ($project instanceof Project) {
                $reason = $this->projectDisplayService->previousStageStartBlockReason($project, $entityInstance::class);
                if ($reason !== null) {
                    throw new PreviousStageAttachmentsIncompleteException($project, $reason);
                }
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            $instance = $responseParameters->get('entity')?->getInstance();
            if ($instance instanceof LifecycleStageInterface) {
                $project = $instance->getProject();
                $definition = $this->stageRegistry->findByEntityClass($instance::class);
                $stage = $this->stageRegistry->stageEnumForEntityClass($instance::class);
                if ($project instanceof Project && $stage !== null) {
                    $responseParameters->set('taskSummary', $this->projectTaskService->getStageTaskSummary($project, $stage));
                }
                if ($definition !== null) {
                    $responseParameters->set('attachmentChecklist', $this->attachmentComplianceService->buildChecklist($definition, $instance));
                    $responseParameters->set('attachmentCompliant', $this->attachmentComplianceService->isCompliant($definition, $instance));
                }
            }
        }

        return $responseParameters;
    }

    /**
     * @return array{0: CollectionField, 1: CollectionField} (form, read-only)
     */
    protected function configureFilesFields(string $help): array
    {
        $formField = CollectionField::new('files', '附件文件')
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded()
            ->setEntryIsComplex(true)
            ->setRequired(false)
            ->setColumns(12)
            ->setHelp($help);

        $readOnlyField = CollectionField::new('files', '附件文件')
            ->setTemplatePath('admin/field/file_collection.html.twig')
            ->hideOnForm()
            ->setHelp($help);

        return [$formField, $readOnlyField];
    }

    private function redirectIfPreviousStageIncomplete(AdminContext $context): ?Response
    {
        $projectId = $context->getRequest()->query->getInt('project');
        if ($projectId <= 0) {
            return null;
        }

        $project = $this->entityManager->find(Project::class, $projectId);
        if (!$project instanceof Project) {
            return null;
        }

        $reason = $this->projectDisplayService->previousStageStartBlockReason($project, static::getEntityFqcn());
        if ($reason === null) {
            return null;
        }

        $this->addFlash('danger', $reason);

        return $this->redirectToProjectDetail($project);
    }

    private function redirectToProjectDetail(?Project $project): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class)
            ->unsetAll()
            ->setController(ProjectCrudController::class)
            ->setAction(Action::INDEX);

        if ($project instanceof Project && $project->getId() !== null) {
            $adminUrlGenerator
                ->setAction(Action::DETAIL)
                ->setEntityId($project->getId());
        }

        return $this->redirect($adminUrlGenerator->generateUrl());
    }
}
