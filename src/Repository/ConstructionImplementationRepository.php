<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ConstructionImplementation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConstructionImplementation>
 */
class ConstructionImplementationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConstructionImplementation::class);
    }

    /**
     * @return ConstructionImplementation[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('ci')
            ->orderBy('ci.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProject(int $projectId): ?ConstructionImplementation
    {
        return $this->createQueryBuilder('ci')
            ->andWhere('ci.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
