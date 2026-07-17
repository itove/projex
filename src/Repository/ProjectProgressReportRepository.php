<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use App\Entity\ProjectProgressReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectProgressReport>
 */
class ProjectProgressReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectProgressReport::class);
    }

    public function findForPeriod(Project $project, \DateTimeImmutable $periodStartDate): ?ProjectProgressReport
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.project = :project')
            ->andWhere('r.periodStartDate = :periodStartDate')
            ->setParameter('project', $project)
            ->setParameter('periodStartDate', $periodStartDate)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<ProjectProgressReport>
     */
    public function findRecentByProject(Project $project, int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.project = :project')
            ->setParameter('project', $project)
            ->orderBy('r.periodStartDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
