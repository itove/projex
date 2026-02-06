<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SettlementAccounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SettlementAccounts>
 */
class SettlementAccountsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SettlementAccounts::class);
    }

    /**
     * @return SettlementAccounts[]
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('sa')
            ->orderBy('sa.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return SettlementAccounts[]
     */
    public function findBySettlementDate(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('sa')
            ->where('sa.settlementDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('sa.settlementDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
