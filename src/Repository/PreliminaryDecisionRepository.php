<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PreliminaryDecision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PreliminaryDecision>
 */
class PreliminaryDecisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreliminaryDecision::class);
    }

    /**
     * @return PreliminaryDecision[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('pd')
            ->orderBy('pd.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProject(int $projectId): ?PreliminaryDecision
    {
        return $this->createQueryBuilder('pd')
            ->andWhere('pd.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
