<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ProjectNavigationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProjectNavigationServiceTest extends KernelTestCase
{
    private ProjectNavigationService $navigationService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->navigationService = $container->get(ProjectNavigationService::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testProjectListUrlContainsExpectedQueryParams(): void
    {
        $totalUrl = $this->navigationService->projectListUrl();
        $this->assertStringContainsString('/admin/project', $totalUrl);
        $this->assertStringNotContainsString('statusGroup=', $totalUrl);
        $this->assertStringNotContainsString('currentStage=', $totalUrl);

        $inProgressUrl = $this->navigationService->projectListUrl(statusGroup: 'in_progress');
        $this->assertStringContainsString('/admin/project', $inProgressUrl);
        $this->assertStringContainsString('statusGroup=in_progress', $inProgressUrl);

        $closedUrl = $this->navigationService->projectListUrl(statusGroup: 'closed');
        $this->assertStringContainsString('statusGroup=closed', $closedUrl);

        $cancelledUrl = $this->navigationService->projectListUrl(status: 'cancelled');
        $this->assertStringContainsString('status=cancelled', $cancelledUrl);

        $stageUrl = $this->navigationService->projectListUrl(currentStage: 3);
        $this->assertStringContainsString('currentStage=3', $stageUrl);

        $orgUrl = $this->navigationService->projectListUrl(orgId: 42);
        $this->assertStringContainsString('orgId=42', $orgUrl);
    }

    public function testBuildDashboardStatisticsLinks(): void
    {
        $links = $this->navigationService->buildDashboardStatisticsLinks();

        $this->assertArrayHasKey('total', $links);
        $this->assertArrayHasKey('in_progress', $links);
        $this->assertArrayHasKey('closed', $links);
        $this->assertArrayHasKey('cancelled', $links);
        $this->assertArrayHasKey('stages', $links);

        $this->assertStringContainsString('statusGroup=in_progress', $links['in_progress']);
        $this->assertStringContainsString('statusGroup=closed', $links['closed']);
        $this->assertStringContainsString('status=cancelled', $links['cancelled']);
        $this->assertStringContainsString('currentStage=1', $links['stages']['preliminary']);
        $this->assertStringContainsString('currentStage=7', $links['stages']['settlement']);
    }

    public function testStageUrlReturnsDetailRouteWhenEntityExists(): void
    {
        $entity = new class {
            public function getId(): int
            {
                return 42;
            }
        };

        $url = $this->navigationService->stageUrl([
            'route' => 'admin_preliminary_decision',
            'entity' => $entity,
        ], 999);

        $this->assertStringContainsString('/admin/preliminary-decision/42', $url);
        $this->assertStringNotContainsString('project=', $url);
    }

    public function testStageUrlReturnsNewRouteWithProjectWhenEntityMissing(): void
    {
        $url = $this->navigationService->stageUrl([
            'route' => 'admin_preliminary_decision',
            'entity' => null,
        ], 123);

        $this->assertStringContainsString('/admin/preliminary-decision/new', $url);
        $this->assertStringContainsString('project=123', $url);
    }

    public function testProjectDetailAnchor(): void
    {
        $this->assertSame('#lifecycle-timeline', $this->navigationService->projectDetailAnchor('lifecycle-timeline'));
        $this->assertSame('#lifecycle-stages', $this->navigationService->projectDetailAnchor('#lifecycle-stages'));
    }

    public function testEnrichStagesWithUrlsAddsUrlToEachStage(): void
    {
        $entity = new class {
            public function getId(): int
            {
                return 7;
            }
        };

        $stages = [
            ['route' => 'admin_preliminary_decision', 'entity' => $entity],
            ['route' => 'admin_project_approval', 'entity' => null],
        ];

        $enriched = $this->navigationService->enrichStagesWithUrls($stages, 123);

        $this->assertCount(2, $enriched);
        $this->assertStringContainsString('/admin/preliminary-decision/7', $enriched[0]['url']);
        $this->assertStringContainsString('/admin/project-approval/new', $enriched[1]['url']);
        $this->assertStringContainsString('project=123', $enriched[1]['url']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
