<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ProjectTask;
use App\Enum\ProjectLifecycleStage;
use PHPUnit\Framework\TestCase;

class ProjectTaskLifecycleStageTest extends TestCase
{
    public function testLifecycleStageIsNullableByDefault(): void
    {
        $task = new ProjectTask();

        $this->assertNull($task->getLifecycleStage());
    }

    public function testLifecycleStageCanBeSetAndCleared(): void
    {
        $task = new ProjectTask();
        $task->setLifecycleStage(ProjectLifecycleStage::Planning);

        $this->assertSame(ProjectLifecycleStage::Planning, $task->getLifecycleStage());

        $task->setLifecycleStage(null);

        $this->assertNull($task->getLifecycleStage());
    }
}
