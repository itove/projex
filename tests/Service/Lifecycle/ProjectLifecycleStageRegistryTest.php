<?php

declare(strict_types=1);

namespace App\Tests\Service\Lifecycle;

use App\Entity\ConstructionImplementation;
use App\Entity\Project;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class ProjectLifecycleStageRegistryTest extends TestCase
{
    private ProjectLifecycleStageRegistry $registry;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->registry = new ProjectLifecycleStageRegistry($entityManager);
    }

    public function testStagesAreOrderedFromPreliminaryToSettlement(): void
    {
        $keys = array_map(static fn ($stage) => $stage->key, $this->registry->all());

        $this->assertSame([
            'preliminary',
            'approval',
            'planning',
            'preparation',
            'implementation',
            'acceptance',
            'settlement',
        ], $keys);
    }

    public function testCountMatchesNumberOfDefinitions(): void
    {
        $this->assertSame(7, $this->registry->count());
        $this->assertCount($this->registry->count(), $this->registry->all());
    }

    public function testFindReturnsMatchingDefinition(): void
    {
        $stage = $this->registry->find('implementation');

        $this->assertNotNull($stage);
        $this->assertSame('施工实施流程', $stage->name);
        $this->assertSame(ConstructionImplementation::class, $stage->entityClass);
        $this->assertSame('admin_construction_implementation', $stage->route);
    }

    public function testFindReturnsNullForUnknownKey(): void
    {
        $this->assertNull($this->registry->find('does_not_exist'));
    }

    public function testIndexOfReturnsZeroBasedPosition(): void
    {
        $this->assertSame(0, $this->registry->indexOf('preliminary'));
        $this->assertSame(6, $this->registry->indexOf('settlement'));
        $this->assertNull($this->registry->indexOf('does_not_exist'));
    }

    public function testUnpersistedProjectHasNoStageEntitiesWithoutQuerying(): void
    {
        $project = new Project();

        foreach ($this->registry->all() as $stage) {
            $this->assertNull($this->registry->getEntity($project, $stage));
            $this->assertNull($this->registry->findEntity($project, $stage->key));
            $this->assertNull($stage->getInfo(null));
        }
    }

    public function testGetEntityLooksUpStageRepositoryByProjectForPersistedProjects(): void
    {
        $project = $this->persistedProject(42);
        $expected = new ConstructionImplementation();

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['project' => $project])
            ->willReturn($expected);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')
            ->with(ConstructionImplementation::class)
            ->willReturn($repository);

        $registry = new ProjectLifecycleStageRegistry($entityManager);

        $this->assertSame($expected, $registry->findEntity($project, 'implementation'));
    }

    private function persistedProject(int $id): Project
    {
        $project = new Project();
        $reflection = new \ReflectionProperty(Project::class, 'id');
        $reflection->setValue($project, $id);

        return $project;
    }
}
