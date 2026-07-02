<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\LifecycleStageInterface;
use App\Entity\Project;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use App\Service\OrgAccessService;
use App\Service\ProjectTaskService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;

/**
 * Base for the seven lifecycle stage CRUD controllers. Adds a stage-scoped
 * task list to the detail page without affecting ProjectTaskCrudController.
 */
abstract class AbstractLifecycleStageCrudController extends AbstractOrgScopedLifecycleCrudController
{
    public function __construct(
        OrgAccessService $orgAccessService,
        protected readonly ProjectTaskService $projectTaskService,
        protected readonly ProjectLifecycleStageRegistry $stageRegistry,
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
                $stage = $this->stageRegistry->stageEnumForEntityClass($instance::class);
                if ($project instanceof Project && $stage !== null) {
                    $responseParameters->set('taskSummary', $this->projectTaskService->getStageTaskSummary($project, $stage));
                }
            }
        }

        return $responseParameters;
    }
}
