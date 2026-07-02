<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Enum\ProjectLifecycleStage;
use App\Repository\ProjectTaskRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class ProjectTaskRepositoryTest extends TestCase
{
    public function testFindByProjectAddsLifecycleStageFilterWhenProvided(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('getResult')->willReturn([]);

        $whereClauses = [];
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('andWhere')->willReturnCallback(function (string $clause) use (&$whereClauses, $queryBuilder) {
            $whereClauses[] = $clause;

            return $queryBuilder;
        });
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('addOrderBy')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $repository = new TestableProjectTaskRepository(
            $this->createMock(ManagerRegistry::class),
            $queryBuilder,
        );

        $tasks = $repository->findByProject(7, null, ProjectLifecycleStage::Approval);

        $this->assertSame([], $tasks);
        $this->assertContains('t.lifecycleStage = :stage', $whereClauses);
    }
}

/**
 * @internal
 */
final class TestableProjectTaskRepository extends ProjectTaskRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly QueryBuilder $testQueryBuilder,
    ) {
        parent::__construct($registry);
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->testQueryBuilder;
    }
}
