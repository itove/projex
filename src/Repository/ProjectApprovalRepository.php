<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProjectApproval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectApproval>
 */
class ProjectApprovalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectApproval::class);
    }

    /**
     * @return ProjectApproval[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('pa')
            ->orderBy('pa.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProject(int $projectId): ?ProjectApproval
    {
        return $this->createQueryBuilder('pa')
            ->andWhere('pa.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
