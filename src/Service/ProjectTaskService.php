<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\Admin\ProjectTaskCrudController;
use App\Entity\Project;
use App\Entity\ProjectTask;
use App\Repository\ProjectTaskRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ProjectTaskService
{
    public function __construct(
        private readonly ProjectTaskRepository $taskRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * @return array{
     *     openCount: int,
     *     overdueCount: int,
     *     tasks: list<ProjectTask>,
     *     listUrl: string,
     *     newUrl: string,
     * }
     */
    public function getProjectTaskSummary(Project $project, int $recentLimit = 10): array
    {
        $projectId = (int) $project->getId();

        return [
            'openCount' => $this->taskRepository->countOpenByProject($projectId),
            'overdueCount' => $this->taskRepository->countOverdueByProject($projectId),
            'tasks' => $this->taskRepository->findByProject($projectId, $recentLimit),
            'listUrl' => $this->buildProjectTaskListUrl($projectId),
            'newUrl' => $this->buildNewTaskUrl($projectId),
        ];
    }

    public function buildProjectTaskListUrl(int $projectId): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(ProjectTaskCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[project][comparison]', '=')
            ->set('filters[project][value]', (string) $projectId)
            ->generateUrl();
    }

    public function buildNewTaskUrl(int $projectId): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(ProjectTaskCrudController::class)
            ->setAction(Action::NEW)
            ->set('project', (string) $projectId)
            ->generateUrl();
    }

    public function buildTaskDetailUrl(ProjectTask $task): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(ProjectTaskCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId((int) $task->getId())
            ->generateUrl();
    }
}
