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
use App\Repository\ProjectRepository;
use Doctrine\Persistence\ManagerRegistry;
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
        ];
    }

    /**
     * Get projects filtered by parameters (4.3.2.2)
     */
    private function getFilteredProjects(array $filters): array
    {
        $qb = $this->projectRepository->createQueryBuilder('p')
            ->leftJoin('p.projectType', 'pt')
            ->leftJoin('p.projectSubtype', 'ps')
            ->leftJoin('p.preliminaryDecision', 'pd')
            ->leftJoin('p.projectApproval', 'pa')
            ->leftJoin('p.planningDesign', 'pld')
            ->leftJoin('p.constructionPreparation', 'cp')
            ->leftJoin('p.constructionImplementation', 'ci')
            ->leftJoin('p.completionAcceptance', 'ca')
            ->leftJoin('p.settlementAccounts', 'sa');

        // Filter by lifecycle stage
        if (!empty($filters['stage'])) {
            $this->applyStageFilter($qb, $filters['stage']);
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

        return $qb->getQuery()->getResult();
    }

    /**
     * Apply lifecycle stage filter
     */
    private function applyStageFilter($qb, string $stage): void
    {
        match ($stage) {
            'preliminary_completed' => $qb->andWhere('pd.id IS NOT NULL'),
            'approval_completed' => $qb->andWhere('pa.id IS NOT NULL'),
            'planning_completed' => $qb->andWhere('pld.id IS NOT NULL'),
            'preparation_completed' => $qb->andWhere('cp.id IS NOT NULL'),
            'implementation_in_progress' => $qb->andWhere('ci.id IS NOT NULL')
                ->andWhere('ca.id IS NULL'),
            'acceptance_in_progress' => $qb->andWhere('ca.id IS NOT NULL')
                ->andWhere('sa.id IS NULL'),
            'settlement_in_progress' => $qb->andWhere('sa.id IS NOT NULL')
                ->andWhere('p.status != :completed')
                ->setParameter('completed', \App\Enum\ProjectStatus::COMPLETED->value),
            'closed' => $qb->andWhere('p.status IN (:closedStatuses)')
                ->setParameter('closedStatuses', [
                    \App\Enum\ProjectStatus::COMPLETED->value,
                    \App\Enum\ProjectStatus::CANCELLED->value
                ]),
            'terminated' => $qb->andWhere('p.status = :cancelled')
                ->setParameter('cancelled', \App\Enum\ProjectStatus::CANCELLED->value),
            default => null,
        };
    }

    /**
     * Calculate statistics for data overview widgets (4.3.2.3)
     */
    private function calculateStatistics(array $projects): array
    {
        $total = count($projects);
        $byStage = [
            'preliminary' => 0,
            'approval' => 0,
            'planning' => 0,
            'preparation' => 0,
            'implementation' => 0,
            'acceptance' => 0,
            'settlement' => 0,
        ];
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
            match ($stageNumber) {
                1 => $byStage['preliminary']++,
                2 => $byStage['approval']++,
                3 => $byStage['planning']++,
                4 => $byStage['preparation']++,
                5 => $byStage['implementation']++,
                6 => $byStage['acceptance']++,
                7 => $byStage['settlement']++,
                default => null,
            };

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
