<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProjectTask;
use App\Enum\ProjectLifecycleStage;
use App\Enum\ProjectTaskStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectTask>
 */
class ProjectTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectTask::class);
    }

    /**
     * @return list<ProjectTask>
     */
    public function findByProject(int $projectId, ?int $limit = null, ?ProjectLifecycleStage $stage = null): array
    {
        $qb = $this->createOrderedQueryBuilder('t')
            ->andWhere('t.project = :projectId')
            ->setParameter('projectId', $projectId);

        if ($stage !== null) {
            $qb->andWhere('t.lifecycleStage = :stage')
                ->setParameter('stage', $stage);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function countOpenByProject(int $projectId, ?ProjectLifecycleStage $stage = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.project = :projectId')
            ->andWhere('t.status IN (:openStatuses)')
            ->setParameter('projectId', $projectId)
            ->setParameter('openStatuses', [
                ProjectTaskStatus::PENDING->value,
                ProjectTaskStatus::IN_PROGRESS->value,
            ]);

        if ($stage !== null) {
            $qb->andWhere('t.lifecycleStage = :stage')
                ->setParameter('stage', $stage);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countOverdueByProject(int $projectId): int
    {
        $today = new \DateTimeImmutable('today');

        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.project = :projectId')
            ->andWhere('t.status IN (:openStatuses)')
            ->andWhere('t.dueDate IS NOT NULL')
            ->andWhere('t.dueDate < :today')
            ->setParameter('projectId', $projectId)
            ->setParameter('openStatuses', [
                ProjectTaskStatus::PENDING->value,
                ProjectTaskStatus::IN_PROGRESS->value,
            ])
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function createOrderedQueryBuilder(string $alias = 't'): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->addOrderBy(
                'CASE '
                ."WHEN {$alias}.status IN ('pending', 'in_progress') THEN 0 "
                .'ELSE 1 END',
                'ASC'
            )
            ->addOrderBy(
                'CASE '
                ."WHEN {$alias}.priority = 'high' THEN 0 "
                ."WHEN {$alias}.priority = 'medium' THEN 1 "
                .'ELSE 2 END',
                'ASC'
            )
            ->addOrderBy("{$alias}.dueDate", 'ASC')
            ->addOrderBy("{$alias}.createdAt", 'DESC');
    }
}
