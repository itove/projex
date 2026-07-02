<?php

declare(strict_types=1);

namespace App\Tests\Service\Lifecycle;

use App\Entity\Project;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use PHPUnit\Framework\TestCase;

class ProjectLifecycleStageRegistryTest extends TestCase
{
    private ProjectLifecycleStageRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ProjectLifecycleStageRegistry();
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
        $this->assertSame('constructionImplementation', $stage->projectProperty);
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

    public function testEachDefinitionExposesAWorkingEntityAccessorAndInfoAccessor(): void
    {
        $project = new Project();

        foreach ($this->registry->all() as $stage) {
            $this->assertNull($stage->getEntity($project));
            $this->assertNull($stage->getInfo(null));
        }
    }
}
