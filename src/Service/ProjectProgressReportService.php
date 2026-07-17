<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\Admin\ProjectProgressReportCrudController;
use App\Entity\Project;
use App\Entity\ProjectProgressReport;
use App\Repository\ProjectProgressReportRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * Encapsulates periodic progress-report lookups and the current-period-only
 * overdue rule: a project is only ever considered overdue for the calendar
 * week/month it is currently in - earlier missed periods are not tracked.
 */
class ProjectProgressReportService
{
    public function __construct(
        private readonly ProjectProgressReportRepository $repository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * @return array{
     *     isOverdue: bool,
     *     period: array{start: \DateTimeImmutable, due: \DateTimeImmutable}|null,
     *     reports: list<ProjectProgressReport>,
     *     listUrl: string,
     *     newUrl: string,
     * }
     */
    public function getProjectProgressReportSummary(Project $project, int $recentLimit = 5): array
    {
        $projectId = (int) $project->getId();

        return [
            'isOverdue' => $this->isReportOverdue($project),
            'period' => $this->getCurrentPeriodRange($project),
            'reports' => $this->getRecentReports($project, $recentLimit),
            'listUrl' => $this->buildProjectReportListUrl($projectId),
            'newUrl' => $this->buildNewReportUrl($projectId),
        ];
    }

    public function buildProjectReportListUrl(int $projectId): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(ProjectProgressReportCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[project][comparison]', '=')
            ->set('filters[project][value]', (string) $projectId)
            ->generateUrl();
    }

    public function buildNewReportUrl(int $projectId): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(ProjectProgressReportCrudController::class)
            ->setAction(Action::NEW)
            ->set('project', (string) $projectId)
            ->generateUrl();
    }

    /**
     * @return array{start: \DateTimeImmutable, due: \DateTimeImmutable}|null
     */
    public function getCurrentPeriodRange(Project $project, ?\DateTimeImmutable $today = null): ?array
    {
        return $project->getCurrentReportingPeriod($today);
    }

    public function isReportOverdue(Project $project, ?\DateTimeImmutable $today = null): bool
    {
        $period = $project->getCurrentReportingPeriod($today);
        if ($period === null) {
            return false;
        }

        $today ??= new \DateTimeImmutable('today', new \DateTimeZone('Asia/Shanghai'));
        if ($today < $period['due']) {
            return false;
        }

        return $this->repository->findForPeriod($project, $period['start']) === null;
    }

    public function getCurrentPeriodReport(Project $project, ?\DateTimeImmutable $today = null): ?ProjectProgressReport
    {
        $period = $project->getCurrentReportingPeriod($today);
        if ($period === null) {
            return null;
        }

        return $this->repository->findForPeriod($project, $period['start']);
    }

    /**
     * @return list<ProjectProgressReport>
     */
    public function getRecentReports(Project $project, int $limit = 5): array
    {
        return $this->repository->findRecentByProject($project, $limit);
    }
}
