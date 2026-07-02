<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Project;
use App\Entity\ProjectTask;
use App\Enum\ProjectLifecycleStage;
use App\Repository\ProjectTaskRepository;
use App\Service\ProjectTaskService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProjectTaskServiceStageSummaryTest extends KernelTestCase
{
    public function testGetStageTaskSummaryUsesStageScopedRepositoryCalls(): void
    {
        self::bootKernel();

        $project = new Project();
        $reflection = new \ReflectionProperty(Project::class, 'id');
        $reflection->setValue($project, 9);

        $expectedTasks = [new ProjectTask()];

        $repository = $this->createMock(ProjectTaskRepository::class);
        $repository->expects($this->once())->method('countOpenByProject')->with(9, ProjectLifecycleStage::Planning)->willReturn(2);
        $repository->expects($this->once())->method('countOverdueByProject')->with(9, ProjectLifecycleStage::Planning)->willReturn(1);
        $repository->expects($this->once())->method('findByProject')->with(9, 10, ProjectLifecycleStage::Planning)->willReturn($expectedTasks);

        $service = new ProjectTaskService(
            $repository,
            static::getContainer()->get(AdminUrlGenerator::class),
        );

        $summary = $service->getStageTaskSummary($project, ProjectLifecycleStage::Planning);

        $this->assertSame(2, $summary['openCount']);
        $this->assertSame(1, $summary['overdueCount']);
        $this->assertSame($expectedTasks, $summary['tasks']);
        $this->assertStringContainsString('stage=planning', $summary['newUrl']);
        $this->assertStringContainsString('lifecycleStage', $summary['listUrl']);
        $this->assertStringContainsString('planning', $summary['listUrl']);
    }
}
