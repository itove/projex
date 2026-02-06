<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CompletionAcceptance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompletionAcceptance>
 */
class CompletionAcceptanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompletionAcceptance::class);
    }

    /**
     * @return CompletionAcceptance[]
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('ca')
            ->orderBy('ca.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CompletionAcceptance[]
     */
    public function findByAcceptanceDate(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('ca')
            ->where('ca.acceptanceDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('ca.acceptanceDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
