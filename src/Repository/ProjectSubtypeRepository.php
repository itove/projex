<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProjectSubtype;
use App\Entity\ProjectType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectSubtype>
 */
class ProjectSubtypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectSubtype::class);
    }

    public function save(ProjectSubtype $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProjectSubtype[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ps.sortOrder', 'ASC')
            ->addOrderBy('ps.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ProjectSubtype[]
     */
    public function findByProjectType(ProjectType $projectType): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.projectType = :type')
            ->andWhere('ps.isActive = :active')
            ->setParameter('type', $projectType)
            ->setParameter('active', true)
            ->orderBy('ps.sortOrder', 'ASC')
            ->addOrderBy('ps.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByCode(string $code): ?ProjectSubtype
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
