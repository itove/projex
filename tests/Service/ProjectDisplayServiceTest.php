<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\ConstructionImplementation;
use App\Entity\ConstructionPreparation;
use App\Entity\PlanningDesign;
use App\Entity\PreliminaryDecision;
use App\Entity\Project;
use App\Entity\ProjectApproval;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use App\Service\ProjectDisplayService;
use PHPUnit\Framework\TestCase;

class ProjectDisplayServiceTest extends TestCase
{
    private ProjectDisplayService $displayService;

    protected function setUp(): void
    {
        $this->displayService = new ProjectDisplayService(new ProjectLifecycleStageRegistry());
    }

    public function testFreshProjectHasNoActiveStage(): void
    {
        $project = new Project();

        $this->assertSame('待前期决策', $this->displayService->getCurrentStageLabel($project));
        $this->assertSame(0, $this->displayService->getLifecycleStageNumber($project));
        $this->assertSame(0, $this->displayService->getOverallProgressPercentage($project));
    }

    public function testCurrentStageTracksTheFurthestStartedStage(): void
    {
        $project = new Project();
        $project->setPreliminaryDecision(new PreliminaryDecision());

        $this->assertSame('前期决策中', $this->displayService->getCurrentStageLabel($project));
        $this->assertSame(1, $this->displayService->getLifecycleStageNumber($project));

        $project->setProjectApproval(new ProjectApproval());

        $this->assertSame('立项中', $this->displayService->getCurrentStageLabel($project));
        $this->assertSame(2, $this->displayService->getLifecycleStageNumber($project));
    }

    public function testStageStatusReflectsIsCompleteRatherThanReflection(): void
    {
        $project = new Project();
        $preliminary = new PreliminaryDecision();
        $project->setPreliminaryDecision($preliminary);

        $stages = $this->displayService->getLifecycleStages($project);
        $this->assertSame('in_progress', $stages[0]['status']);

        $preliminary->setCompletionDate(new \DateTimeImmutable('2026-01-01'));

        $stages = $this->displayService->getLifecycleStages($project);
        $this->assertSame('completed', $stages[0]['status']);
        $this->assertSame('not_started', $stages[1]['status']);
    }

    public function testOnlyImplementationStageCarriesAProgressKey(): void
    {
        $project = new Project();
        $project->setPreliminaryDecision(new PreliminaryDecision());
        $project->setConstructionImplementation((new ConstructionImplementation())->setCurrentProgress(42));

        $stages = $this->displayService->getLifecycleStages($project);

        $this->assertArrayNotHasKey('progress', $stages[0]);
        $this->assertArrayHasKey('progress', $stages[4]);
        $this->assertSame(42, $stages[4]['progress']);
    }

    public function testLifecycleStagesExposeRegistryRequirementsHint(): void
    {
        $project = new Project();

        $stages = $this->displayService->getLifecycleStages($project);

        $this->assertSame('需上传项目建议书、可行性研究报告等文档', $stages[0]['requirementsHint']);
        $this->assertSame('需上传竣工结算书、决算报告、审计报告等', $stages[6]['requirementsHint']);
    }

    public function testOverallProgressAddsPartialCreditWhileImplementing(): void
    {
        $project = new Project();
        $project->setPreliminaryDecision(new PreliminaryDecision());
        $project->setProjectApproval(new ProjectApproval());
        $project->setPlanningDesign(new PlanningDesign());
        $project->setConstructionPreparation(new ConstructionPreparation());
        $project->setConstructionImplementation((new ConstructionImplementation())->setCurrentProgress(50));

        // 5 of 7 stages reached (~71%) plus half credit for the 6th (~7%) = 78%
        $this->assertSame(78, $this->displayService->getOverallProgressPercentage($project));
    }

    public function testOverallProgressReaches100WhenEveryStageStarted(): void
    {
        $project = new Project();
        $project->setPreliminaryDecision(new PreliminaryDecision());
        $project->setProjectApproval(new ProjectApproval());
        $project->setPlanningDesign(new PlanningDesign());
        $project->setConstructionPreparation(new ConstructionPreparation());
        $project->setConstructionImplementation(new ConstructionImplementation());
        $project->setCompletionAcceptance(new \App\Entity\CompletionAcceptance());
        $project->setSettlementAccounts(new \App\Entity\SettlementAccounts());

        $this->assertSame(7, $this->displayService->getLifecycleStageNumber($project));
        $this->assertSame(100, $this->displayService->getOverallProgressPercentage($project));
    }

    public function testGetStageFileCountHandlesNullAndEmptyCollections(): void
    {
        $this->assertSame(0, $this->displayService->getStageFileCount(null));
        $this->assertSame(0, $this->displayService->getStageFileCount(new PreliminaryDecision()));
    }
}
