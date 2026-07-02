<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\Admin\ProjectCrudController;
use App\Enum\ProjectStatus;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
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
        private readonly ProjectLifecycleStageRegistry $stageRegistry,
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
        $stages = [];
        foreach ($this->stageRegistry->all() as $index => $definition) {
            $stages[$definition->key] = $this->projectListUrl(currentStage: $index + 1);
        }

        return [
            'total' => $this->projectListUrl(),
            'in_progress' => $this->projectListUrl(statusGroup: 'in_progress'),
            'closed' => $this->projectListUrl(statusGroup: 'closed'),
            'cancelled' => $this->projectListUrl(status: ProjectStatus::CANCELLED->value),
            'stages' => $stages,
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
        if ($currentStage >= 1 && $currentStage <= $this->stageRegistry->count()) {
            $this->applyCurrentStageFilter($qb, $currentStage, $projectAlias);
        }
    }

    /**
     * A project is "at" stage N when stage N's entity exists and either it's
     * the last stage, or the next stage's entity doesn't exist yet.
     */
    private function applyCurrentStageFilter(QueryBuilder $qb, int $stage, string $projectAlias): void
    {
        $definitions = $this->stageRegistry->all();
        $stageIndex = $stage - 1;
        if (!isset($definitions[$stageIndex])) {
            return;
        }

        $aliases = [];
        foreach ($definitions as $index => $definition) {
            $alias = 'nav_stage_' . $index;
            $aliases[] = $alias;
            $qb->leftJoin(sprintf('%s.%s', $projectAlias, $definition->projectProperty), $alias);
        }

        $qb->andWhere(sprintf('%s.id IS NOT NULL', $aliases[$stageIndex]));

        $nextAlias = $aliases[$stageIndex + 1] ?? null;
        if ($nextAlias !== null) {
            $qb->andWhere(sprintf('%s.id IS NULL', $nextAlias));
        }
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
