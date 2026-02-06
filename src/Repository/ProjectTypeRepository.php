<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProjectType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectType>
 */
class ProjectTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectType::class);
    }

    public function save(ProjectType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProjectType[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pt.sortOrder', 'ASC')
            ->addOrderBy('pt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByCode(string $code): ?ProjectType
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
