<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PlanningDesign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlanningDesign>
 */
class PlanningDesignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanningDesign::class);
    }

    /**
     * @return PlanningDesign[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('pd')
            ->orderBy('pd.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProject(int $projectId): ?PlanningDesign
    {
        return $this->createQueryBuilder('pd')
            ->andWhere('pd.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
