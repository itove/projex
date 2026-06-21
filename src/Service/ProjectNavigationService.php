<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\Admin\ProjectCrudController;
use App\Enum\ProjectStatus;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjectNavigationService
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function projectListUrl(
        ?string $status = null,
        ?string $statusGroup = null,
        ?int $currentStage = null,
        ?int $orgId = null,
    ): string {
        $generator = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(ProjectCrudController::class)
            ->setAction(Action::INDEX);

        if ($status !== null) {
            $generator->set('status', $status);
        }

        if ($statusGroup !== null) {
            $generator->set('statusGroup', $statusGroup);
        }

        if ($currentStage !== null) {
            $generator->set('currentStage', (string) $currentStage);
        }

        if ($orgId !== null) {
            $generator->set('orgId', (string) $orgId);
        }

        return $generator->generateUrl();
    }

    /**
     * @return array<string, string>
     */
    public function buildDashboardStatisticsLinks(): array
    {
        return [
            'total' => $this->projectListUrl(),
            'in_progress' => $this->projectListUrl(statusGroup: 'in_progress'),
            'closed' => $this->projectListUrl(statusGroup: 'closed'),
            'cancelled' => $this->projectListUrl(status: ProjectStatus::CANCELLED->value),
            'stages' => [
                'preliminary' => $this->projectListUrl(currentStage: 1),
                'approval' => $this->projectListUrl(currentStage: 2),
                'planning' => $this->projectListUrl(currentStage: 3),
                'preparation' => $this->projectListUrl(currentStage: 4),
                'implementation' => $this->projectListUrl(currentStage: 5),
                'acceptance' => $this->projectListUrl(currentStage: 6),
                'settlement' => $this->projectListUrl(currentStage: 7),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $stage
     */
    public function stageUrl(array $stage, int $projectId): string
    {
        $route = $stage['route'] ?? '';

        if (($stage['entity'] ?? null) !== null) {
            return $this->urlGenerator->generate($route . '_detail', [
                'entityId' => $stage['entity']->getId(),
            ]);
        }

        return $this->urlGenerator->generate($route . '_new') . '?project=' . $projectId;
    }

    public function projectDetailAnchor(string $anchor): string
    {
        return '#' . ltrim($anchor, '#');
    }

    /**
     * @param list<array<string, mixed>> $stages
     *
     * @return array<string, string>
     */
    public function buildProjectDetailSummaryLinks(array $stages, int $projectId): array
    {
        return [
            'stageProgress' => $this->projectDetailAnchor('lifecycle-timeline'),
            'currentStage' => $this->resolveCurrentStageUrl($stages, $projectId),
            'completedStages' => $this->projectDetailAnchor('lifecycle-stages'),
            'totalFiles' => $this->projectDetailAnchor('lifecycle-stages'),
        ];
    }

    /**
     * @param list<array<string, mixed>> $stages
     */
    public function enrichStagesWithUrls(array $stages, int $projectId): array
    {
        foreach ($stages as $index => $stage) {
            $stages[$index]['url'] = $this->stageUrl($stage, $projectId);
        }

        return $stages;
    }

    public function applyListFilters(QueryBuilder $qb, Request $request, string $projectAlias = 'entity'): void
    {
        $statusGroup = $request->query->getString('statusGroup');
        if ($statusGroup === 'in_progress') {
            $qb->andWhere(sprintf('%s.status IN (:inProgressStatuses)', $projectAlias))
                ->setParameter('inProgressStatuses', [
                    ProjectStatus::REGISTERED->value,
                    ProjectStatus::IN_PRELIMINARY_DECISION->value,
                    ProjectStatus::PRELIMINARY_APPROVED->value,
                    ProjectStatus::IN_PROGRESS->value,
                ]);
        } elseif ($statusGroup === 'closed') {
            $qb->andWhere(sprintf('%s.status IN (:closedStatuses)', $projectAlias))
                ->setParameter('closedStatuses', [
                    ProjectStatus::COMPLETED->value,
                    ProjectStatus::CANCELLED->value,
                ]);
        }

        $status = $request->query->getString('status');
        if ($status !== '' && $statusGroup === '') {
            $qb->andWhere(sprintf('%s.status = :filterStatus', $projectAlias))
                ->setParameter('filterStatus', $status);
        }

        $currentStage = $request->query->getInt('currentStage');
        if ($currentStage >= 1 && $currentStage <= 7) {
            $this->applyCurrentStageFilter($qb, $currentStage, $projectAlias);
        }
    }

    private function applyCurrentStageFilter(QueryBuilder $qb, int $stage, string $projectAlias): void
    {
        $qb->leftJoin(sprintf('%s.preliminaryDecision', $projectAlias), 'nav_pd')
            ->leftJoin(sprintf('%s.projectApproval', $projectAlias), 'nav_pa')
            ->leftJoin(sprintf('%s.planningDesign', $projectAlias), 'nav_pld')
            ->leftJoin(sprintf('%s.constructionPreparation', $projectAlias), 'nav_cp')
            ->leftJoin(sprintf('%s.constructionImplementation', $projectAlias), 'nav_ci')
            ->leftJoin(sprintf('%s.completionAcceptance', $projectAlias), 'nav_ca')
            ->leftJoin(sprintf('%s.settlementAccounts', $projectAlias), 'nav_sa');

        match ($stage) {
            1 => $qb->andWhere('nav_pd.id IS NOT NULL AND nav_pa.id IS NULL'),
            2 => $qb->andWhere('nav_pa.id IS NOT NULL AND nav_pld.id IS NULL'),
            3 => $qb->andWhere('nav_pld.id IS NOT NULL AND nav_cp.id IS NULL'),
            4 => $qb->andWhere('nav_cp.id IS NOT NULL AND nav_ci.id IS NULL'),
            5 => $qb->andWhere('nav_ci.id IS NOT NULL AND nav_ca.id IS NULL'),
            6 => $qb->andWhere('nav_ca.id IS NOT NULL AND nav_sa.id IS NULL'),
            7 => $qb->andWhere('nav_sa.id IS NOT NULL'),
            default => null,
        };
    }

    /**
     * @param list<array<string, mixed>> $stages
     */
    private function resolveCurrentStageUrl(array $stages, int $projectId): string
    {
        foreach ($stages as $stage) {
            if (($stage['status'] ?? '') === 'in_progress') {
                return $this->stageUrl($stage, $projectId);
            }
        }

        for ($i = count($stages) - 1; $i >= 0; $i--) {
            if (($stages[$i]['entity'] ?? null) !== null) {
                return $this->stageUrl($stages[$i], $projectId);
            }
        }

        return $this->stageUrl($stages[0] ?? ['route' => 'admin_preliminary_decision', 'entity' => null], $projectId);
    }
}
