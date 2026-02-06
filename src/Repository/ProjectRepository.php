<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use App\Enum\ProjectStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function generateProjectNumber(string $prefix, int $year): string
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('MAX(p.projectNumber)')
            ->where($qb->expr()->like('p.projectNumber', ':pattern'))
            ->setParameter('pattern', $prefix . $year . '%');

        $lastNumber = $qb->getQuery()->getSingleScalarResult();

        if ($lastNumber === null) {
            $sequence = 1;
        } else {
            // Extract the sequence number from the last project number
            // Format: XM2026001 -> extract "001"
            $sequence = (int) substr($lastNumber, strlen($prefix . $year)) + 1;
        }

        return sprintf('%s%d%03d', $prefix, $year, $sequence);
    }

    public function projectNumberExists(string $projectNumber): bool
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.projectNumber = :projectNumber')
            ->setParameter('projectNumber', $projectNumber)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * @return Project[]
     */
    public function findByStatus(ProjectStatus $status): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', $status)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
