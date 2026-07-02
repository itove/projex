<?php
/**
 * vim:ft=php et ts=4 sts=4
 * @author Al Zee <z@alz.ee>
 * @version
 * @todo
 */

namespace App\Service;

use App\DTO\ProjectCardDTO;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;

class DashboardData
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private ChartBuilderInterface $chartBuilder,
        private ProjectRepository $projectRepository,
        private ProjectDisplayService $displayService,
        private RequestStack $requestStack,
        private Security $security,
        private OrgAccessService $orgAccessService,
        private ProjectNavigationService $projectNavigationService,
        private ProjectLifecycleStageRegistry $stageRegistry,
    ) {
    }

    public function get(): array
    {
        $tz = new \DateTimeZone('Asia/Shanghai');
        $request = $this->requestStack->getCurrentRequest();

        // Get filter parameters (4.3.2.2)
        $filters = [
            'stage' => $request?->query->get('stage', '') ?? '',
            'status' => $request?->query->get('status', '') ?? '',
            'type' => $request?->query->get('type', '') ?? '',
            'nature' => $request?->query->get('nature', '') ?? '',
            'search' => $request?->query->get('search', '') ?? '',
            'sortBy' => $request?->query->get('sortBy', 'updatedAt') ?? 'updatedAt',
            'sortOrder' => $request?->query->get('sortOrder', 'DESC') ?? 'DESC',
        ];

        // Get projects based on filters
        $projects = $this->getFilteredProjects($filters);

        // Convert to DTOs for display
        $projectCards = array_map(
            fn(Project $project) => new ProjectCardDTO($project, $this->displayService),
            $projects
        );

        // Calculate statistics (4.3.2.3)
        $statistics = $this->calculateStatistics($projects);

        return [
            'charts' => [],
            'projectCards' => $projectCards,
            'filters' => $filters,
            'statistics' => $statistics,
            'slideProjects' => $this->getSlideProjects(),
            'statisticsLinks' => $this->projectNavigationService->buildDashboardStatisticsLinks(),
        ];
    }

    private function getSlideProjects(): array
    {
        $qb = $this->projectRepository->createQueryBuilder('p')
            ->where('p.titleImageName IS NOT NULL')
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults(5);

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $this->orgAccessService->applyProjectOrgScope($qb, $user, 'p');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get projects filtered by parameters (4.3.2.2)
     */
    private function getFilteredProjects(array $filters): array
    {
        $qb = $this->projectRepository->createQueryBuilder('p')
            ->leftJoin('p.projectType', 'pt')
            ->leftJoin('p.projectSubtype', 'ps');

        $stageAliases = [];
        foreach ($this->stageRegistry->all() as $index => $definition) {
            $alias = 'dash_stage_' . $index;
            $stageAliases[$definition->key] = $alias;
            $qb->leftJoin(sprintf('p.%s', $definition->projectProperty), $alias);
        }

        // Filter by lifecycle stage
        if (!empty($filters['stage'])) {
            $this->applyStageFilter($qb, $filters['stage'], $stageAliases);
        }

        // Filter by project status
        if (!empty($filters['status'])) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $filters['status']);
        }

        // Filter by project type
        if (!empty($filters['type'])) {
            $qb->andWhere('pt.id = :typeId')
                ->setParameter('typeId', $filters['type']);
        }

        // Filter by project nature
        if (!empty($filters['nature'])) {
            $qb->andWhere('p.projectNature = :nature')
                ->setParameter('nature', $filters['nature']);
        }

        // Search by project number (exact) or project name/personnel (fuzzy)
        if (!empty($filters['search'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'p.projectNumber = :exactSearch',
                    'p.projectName LIKE :fuzzySearch',
                    'p.leaderName LIKE :fuzzySearch',
                    'p.registrantName LIKE :fuzzySearch'
                )
            )
                ->setParameter('exactSearch', $filters['search'])
                ->setParameter('fuzzySearch', '%' . $filters['search'] . '%');
        }

        // Apply sorting
        $sortBy = $filters['sortBy'];
        $sortOrder = strtoupper($filters['sortOrder']) === 'ASC' ? 'ASC' : 'DESC';

        $validSortFields = ['updatedAt', 'createdAt', 'projectName', 'budget'];
        if (in_array($sortBy, $validSortFields, true)) {
            $qb->orderBy('p.' . $sortBy, $sortOrder);
        } else {
            $qb->orderBy('p.updatedAt', 'DESC');
        }

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $this->orgAccessService->applyProjectOrgScope($qb, $user, 'p');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Apply lifecycle stage filter.
     *
     * Recognizes two generic filter value shapes derived from the stage
     * registry: "{stageKey}_completed" (that stage's entity exists) and
     * "{stageKey}_in_progress" (that stage's entity exists but the next
     * stage hasn't started yet - or, for the final stage, the project isn't
     * marked completed yet), plus the status-based "closed"/"terminated".
     *
     * @param array<string, string> $stageAliases stage key => join alias
     */
    private function applyStageFilter(QueryBuilder $qb, string $stage, array $stageAliases): void
    {
        if ($stage === 'closed') {
            $qb->andWhere('p.status IN (:closedStatuses)')
                ->setParameter('closedStatuses', [
                    \App\Enum\ProjectStatus::COMPLETED->value,
                    \App\Enum\ProjectStatus::CANCELLED->value,
                ]);

            return;
        }

        if ($stage === 'terminated') {
            $qb->andWhere('p.status = :cancelled')
                ->setParameter('cancelled', \App\Enum\ProjectStatus::CANCELLED->value);

            return;
        }

        $definitions = $this->stageRegistry->all();

        foreach ($definitions as $index => $definition) {
            $alias = $stageAliases[$definition->key];

            if ($stage === $definition->key . '_completed') {
                $qb->andWhere(sprintf('%s.id IS NOT NULL', $alias));

                return;
            }

            if ($stage === $definition->key . '_in_progress') {
                $qb->andWhere(sprintf('%s.id IS NOT NULL', $alias));

                $nextDefinition = $definitions[$index + 1] ?? null;
                if ($nextDefinition !== null) {
                    $qb->andWhere(sprintf('%s.id IS NULL', $stageAliases[$nextDefinition->key]));
                } else {
                    $qb->andWhere('p.status != :notCompletedStatus')
                        ->setParameter('notCompletedStatus', \App\Enum\ProjectStatus::COMPLETED->value);
                }

                return;
            }
        }
    }

    /**
     * Calculate statistics for data overview widgets (4.3.2.3)
     */
    private function calculateStatistics(array $projects): array
    {
        $total = count($projects);
        $stageDefinitions = $this->stageRegistry->all();
        $byStage = array_fill_keys(array_map(
            static fn ($definition) => $definition->key,
            $stageDefinitions
        ), 0);
        $byStatus = [
            'draft' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'cancelled' => 0,
        ];
        $closed = 0;

        foreach ($projects as $project) {
            // Count by lifecycle stage
            $stageNumber = $this->displayService->getLifecycleStageNumber($project);
            if ($stageNumber > 0 && isset($stageDefinitions[$stageNumber - 1])) {
                $byStage[$stageDefinitions[$stageNumber - 1]->key]++;
            }

            // Count by status
            $status = $project->getStatus();
            match ($status) {
                \App\Enum\ProjectStatus::DRAFT => $byStatus['draft']++,
                \App\Enum\ProjectStatus::REGISTERED,
                \App\Enum\ProjectStatus::IN_PRELIMINARY_DECISION,
                \App\Enum\ProjectStatus::PRELIMINARY_APPROVED,
                \App\Enum\ProjectStatus::IN_PROGRESS => $byStatus['in_progress']++,
                \App\Enum\ProjectStatus::COMPLETED => $byStatus['completed']++,
                \App\Enum\ProjectStatus::CANCELLED => $byStatus['cancelled']++,
            };

            // Count closed projects
            if ($this->displayService->isProjectClosed($project)) {
                $closed++;
            }
        }

        return [
            'total' => $total,
            'byStage' => $byStage,
            'byStatus' => $byStatus,
            'closed' => $closed,
        ];
    }
}
