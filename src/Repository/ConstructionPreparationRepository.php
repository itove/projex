<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ConstructionPreparation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConstructionPreparation>
 */
class ConstructionPreparationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConstructionPreparation::class);
    }

    /**
     * @return ConstructionPreparation[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('cp')
            ->orderBy('cp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProject(int $projectId): ?ConstructionPreparation
    {
        return $this->createQueryBuilder('cp')
            ->andWhere('cp.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
