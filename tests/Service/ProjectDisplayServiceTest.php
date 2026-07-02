<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\CompletionAcceptance;
use App\Entity\ConstructionImplementation;
use App\Entity\ConstructionPreparation;
use App\Entity\PlanningDesign;
use App\Entity\PreliminaryDecision;
use App\Entity\Project;
use App\Entity\ProjectApproval;
use App\Entity\SettlementAccounts;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use App\Service\ProjectDisplayService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class ProjectDisplayServiceTest extends TestCase
{
    /**
     * Stage entities keyed by entity class, consulted by the mocked
     * registry's EntityManager. Tests mutate this array in place to
     * simulate a project progressing through stages, since Project no
     * longer holds direct references to its stage entities.
     *
     * @var array<class-string, object>
     */
    private array $entitiesByClass;

    private Project $project;

    private ProjectDisplayService $displayService;

    protected function setUp(): void
    {
        $this->entitiesByClass = [];
        $this->project = $this->persistedProject(1);
        $this->displayService = new ProjectDisplayService($this->mockRegistry());
    }

    public function testFreshProjectHasNoActiveStage(): void
    {
        $this->assertSame('待前期决策', $this->displayService->getCurrentStageLabel($this->project));
        $this->assertSame(0, $this->displayService->getLifecycleStageNumber($this->project));
        $this->assertSame(0, $this->displayService->getOverallProgressPercentage($this->project));
    }

    public function testCurrentStageTracksTheFurthestStartedStage(): void
    {
        $this->entitiesByClass[PreliminaryDecision::class] = new PreliminaryDecision();

        $this->assertSame('前期决策中', $this->displayService->getCurrentStageLabel($this->project));
        $this->assertSame(1, $this->displayService->getLifecycleStageNumber($this->project));

        $this->entitiesByClass[ProjectApproval::class] = new ProjectApproval();

        $this->assertSame('立项中', $this->displayService->getCurrentStageLabel($this->project));
        $this->assertSame(2, $this->displayService->getLifecycleStageNumber($this->project));
    }

    public function testStageStatusReflectsIsCompleteRatherThanReflection(): void
    {
        $preliminary = new PreliminaryDecision();
        $this->entitiesByClass[PreliminaryDecision::class] = $preliminary;

        $stages = $this->displayService->getLifecycleStages($this->project);
        $this->assertSame('in_progress', $stages[0]['status']);

        $preliminary->setCompletionDate(new \DateTimeImmutable('2026-01-01'));

        $stages = $this->displayService->getLifecycleStages($this->project);
        $this->assertSame('completed', $stages[0]['status']);
        $this->assertSame('not_started', $stages[1]['status']);
    }

    public function testOnlyImplementationStageCarriesAProgressKey(): void
    {
        $this->entitiesByClass[PreliminaryDecision::class] = new PreliminaryDecision();
        $this->entitiesByClass[ConstructionImplementation::class] = (new ConstructionImplementation())->setCurrentProgress(42);

        $stages = $this->displayService->getLifecycleStages($this->project);

        $this->assertArrayNotHasKey('progress', $stages[0]);
        $this->assertArrayHasKey('progress', $stages[4]);
        $this->assertSame(42, $stages[4]['progress']);
    }

    public function testLifecycleStagesExposeRegistryRequirementsHint(): void
    {
        $stages = $this->displayService->getLifecycleStages($this->project);

        $this->assertSame('需上传项目建议书、可行性研究报告等文档', $stages[0]['requirementsHint']);
        $this->assertSame('需上传竣工结算书、决算报告、审计报告等', $stages[6]['requirementsHint']);
    }

    public function testOverallProgressAddsPartialCreditWhileImplementing(): void
    {
        $this->entitiesByClass[PreliminaryDecision::class] = new PreliminaryDecision();
        $this->entitiesByClass[ProjectApproval::class] = new ProjectApproval();
        $this->entitiesByClass[PlanningDesign::class] = new PlanningDesign();
        $this->entitiesByClass[ConstructionPreparation::class] = new ConstructionPreparation();
        $this->entitiesByClass[ConstructionImplementation::class] = (new ConstructionImplementation())->setCurrentProgress(50);

        // 5 of 7 stages reached (~71%) plus half credit for the 6th (~7%) = 78%
        $this->assertSame(78, $this->displayService->getOverallProgressPercentage($this->project));
    }

    public function testOverallProgressReaches100WhenEveryStageStarted(): void
    {
        $this->entitiesByClass[PreliminaryDecision::class] = new PreliminaryDecision();
        $this->entitiesByClass[ProjectApproval::class] = new ProjectApproval();
        $this->entitiesByClass[PlanningDesign::class] = new PlanningDesign();
        $this->entitiesByClass[ConstructionPreparation::class] = new ConstructionPreparation();
        $this->entitiesByClass[ConstructionImplementation::class] = new ConstructionImplementation();
        $this->entitiesByClass[CompletionAcceptance::class] = new CompletionAcceptance();
        $this->entitiesByClass[SettlementAccounts::class] = new SettlementAccounts();

        $this->assertSame(7, $this->displayService->getLifecycleStageNumber($this->project));
        $this->assertSame(100, $this->displayService->getOverallProgressPercentage($this->project));
    }

    public function testGetStageFileCountHandlesNullAndEmptyCollections(): void
    {
        $this->assertSame(0, $this->displayService->getStageFileCount(null));
        $this->assertSame(0, $this->displayService->getStageFileCount(new PreliminaryDecision()));
    }

    private function mockRegistry(): ProjectLifecycleStageRegistry
    {
        $project = $this->project;
        $entitiesByClass = &$this->entitiesByClass;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturnCallback(
            function (string $class) use ($project, &$entitiesByClass): EntityRepository {
                $repository = $this->createMock(EntityRepository::class);
                $repository->method('findOneBy')->willReturnCallback(
                    static function (array $criteria) use ($class, $project, &$entitiesByClass) {
                        if (($criteria['project'] ?? null) !== $project) {
                            return null;
                        }

                        return $entitiesByClass[$class] ?? null;
                    }
                );

                return $repository;
            }
        );

        return new ProjectLifecycleStageRegistry($entityManager);
    }

    private function persistedProject(int $id): Project
    {
        $project = new Project();
        $reflection = new \ReflectionProperty(Project::class, 'id');
        $reflection->setValue($project, $id);

        return $project;
    }
}
