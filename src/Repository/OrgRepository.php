<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Org;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Org>
 */
class OrgRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Org::class);
    }

    /**
     * @return list<int>
     */
    public function findDescendantIds(int $rootOrgId): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            WITH RECURSIVE org_tree AS (
                SELECT id FROM org WHERE id = :rootId
                UNION ALL
                SELECT o.id FROM org o
                INNER JOIN org_tree t ON o.parent_id = t.id
            )
            SELECT id FROM org_tree
        SQL;

        $ids = $connection->fetchFirstColumn($sql, ['rootId' => $rootOrgId]);

        return array_map('intval', $ids);
    }

    /**
     * @return list<int>
     */
    public function findAncestorIds(int $orgId): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            WITH RECURSIVE org_ancestors AS (
                SELECT id, parent_id FROM org WHERE id = :orgId
                UNION ALL
                SELECT o.id, o.parent_id FROM org o
                INNER JOIN org_ancestors a ON o.id = a.parent_id
            )
            SELECT id FROM org_ancestors
        SQL;

        $ids = $connection->fetchFirstColumn($sql, ['orgId' => $orgId]);

        return array_map('intval', $ids);
    }

    public function isDescendantOf(int $candidateId, int $ancestorId): bool
    {
        if ($candidateId === $ancestorId) {
            return true;
        }

        return in_array($candidateId, $this->findDescendantIds($ancestorId), true);
    }
}
