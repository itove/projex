<?php

declare(strict_types=1);

namespace App\Tests\Enum;

use App\Enum\ProjectLifecycleStage;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ProjectLifecycleStageTest extends TestCase
{
    public function testTryFromKeyReturnsEnumForKnownKeys(): void
    {
        $this->assertSame(ProjectLifecycleStage::Preliminary, ProjectLifecycleStage::tryFromKey('preliminary'));
        $this->assertSame(ProjectLifecycleStage::Settlement, ProjectLifecycleStage::tryFromKey('settlement'));
    }

    public function testTryFromKeyReturnsNullForUnknownOrEmptyKeys(): void
    {
        $this->assertNull(ProjectLifecycleStage::tryFromKey(null));
        $this->assertNull(ProjectLifecycleStage::tryFromKey(''));
        $this->assertNull(ProjectLifecycleStage::tryFromKey('does_not_exist'));
    }

    public function testLabelsAreNonEmpty(): void
    {
        foreach (ProjectLifecycleStage::cases() as $stage) {
            $this->assertNotSame('', $stage->label());
        }
    }

    public function testEnumValuesMatchRegistryKeys(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $registry = new ProjectLifecycleStageRegistry($entityManager);

        $registryKeys = array_map(static fn ($definition) => $definition->key, $registry->all());
        $enumValues = array_map(static fn (ProjectLifecycleStage $stage) => $stage->value, ProjectLifecycleStage::cases());

        $this->assertSame($registryKeys, $enumValues);
    }
}
