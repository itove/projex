<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\LifecycleStageInterface;
use App\Entity\Project;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use App\Service\Lifecycle\StageAttachmentComplianceService;
use App\Service\OrgAccessService;
use App\Service\ProjectTaskService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

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
    ) {
        parent::__construct($orgAccessService);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/detail', 'admin/lifecycle_stage/detail.html.twig');
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
}
