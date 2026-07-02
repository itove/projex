<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\ProjectTask;
use App\Enum\ProjectLifecycleStage;
use App\Enum\ProjectTaskStatus;
use App\Service\ProjectTaskService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProjectTaskServiceTest extends KernelTestCase
{
    private ProjectTaskService $taskService;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->taskService = static::getContainer()->get(ProjectTaskService::class);
    }

    public function testDurationDaysCalculatedInclusive(): void
    {
        $task = new ProjectTask();
        $task->setTitle('测试任务');
        $task->setStartDate(new \DateTimeImmutable('2026-06-01'));
        $task->setEndDate(new \DateTimeImmutable('2026-06-03'));
        $task->syncDerivedFields();

        $this->assertSame(3, $task->getDurationDays());
    }

    public function testDurationDaysClearedWhenDatesMissing(): void
    {
        $task = new ProjectTask();
        $task->setTitle('测试任务');
        $task->setStartDate(new \DateTimeImmutable('2026-06-01'));
        $task->syncDerivedFields();

        $this->assertNull($task->getDurationDays());
    }

    public function testCompletedAtSetWhenStatusDone(): void
    {
        $task = new ProjectTask();
        $task->setTitle('测试任务');
        $task->setStatus(ProjectTaskStatus::DONE);
        $task->syncDerivedFields();

        $this->assertNotNull($task->getCompletedAt());
    }

    public function testCompletedAtClearedWhenStatusNotDone(): void
    {
        $task = new ProjectTask();
        $task->setTitle('测试任务');
        $task->setStatus(ProjectTaskStatus::DONE);
        $task->syncDerivedFields();
        $task->setStatus(ProjectTaskStatus::IN_PROGRESS);
        $task->syncDerivedFields();

        $this->assertNull($task->getCompletedAt());
    }

    public function testBuildTaskUrls(): void
    {
        $listUrl = $this->taskService->buildProjectTaskListUrl(42);
        $newUrl = $this->taskService->buildNewTaskUrl(42);

        $this->assertStringContainsString('project', $listUrl);
        $this->assertStringContainsString('filters', $listUrl);
        $this->assertStringContainsString('42', $listUrl);
        $this->assertStringContainsString('project=42', $newUrl);
    }

    public function testBuildNewTaskUrlIncludesStageWhenProvided(): void
    {
        $newUrl = $this->taskService->buildNewTaskUrl(42, ProjectLifecycleStage::Preparation->value);

        $this->assertStringContainsString('project=42', $newUrl);
        $this->assertStringContainsString('stage=preparation', $newUrl);
    }

    public function testBuildNewTaskUrlOmitsStageWhenEmpty(): void
    {
        $newUrl = $this->taskService->buildNewTaskUrl(42, '');

        $this->assertStringNotContainsString('stage=', $newUrl);
    }

    public function testBuildProjectTaskListUrlIncludesStageFilterWhenProvided(): void
    {
        $listUrl = $this->taskService->buildProjectTaskListUrl(42, ProjectLifecycleStage::Approval);

        $this->assertStringContainsString('filters', $listUrl);
        $this->assertStringContainsString('lifecycleStage', $listUrl);
        $this->assertStringContainsString('approval', $listUrl);
    }
}
