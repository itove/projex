<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Project;
use App\Entity\ProjectProgressReport;
use App\Enum\ProjectProgressReportInterval;
use App\Repository\ProjectProgressReportRepository;
use App\Service\ProjectProgressReportService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProjectProgressReportServiceTest extends KernelTestCase
{
    private ProjectProgressReportRepository&\PHPUnit\Framework\MockObject\MockObject $repository;

    private ProjectProgressReportService $service;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = $this->createMock(ProjectProgressReportRepository::class);
        $this->service = new ProjectProgressReportService(
            $this->repository,
            static::getContainer()->get(AdminUrlGenerator::class),
        );
    }

    public function testGetCurrentPeriodRangeIsNullWhenIntervalNotConfigured(): void
    {
        $project = $this->createProject(null);

        $this->assertNull($this->service->getCurrentPeriodRange($project, new \DateTimeImmutable('2026-03-01')));
    }

    public function testGetCurrentPeriodRangeForWeek(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::WEEK);

        $period = $this->service->getCurrentPeriodRange($project, new \DateTimeImmutable('2026-07-16'));

        $this->assertNotNull($period);
        $this->assertSame('2026-07-13', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-07-19', $period['due']->format('Y-m-d'));
    }

    public function testGetCurrentPeriodRangeForMonth(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::MONTH);

        $period = $this->service->getCurrentPeriodRange($project, new \DateTimeImmutable('2026-07-16'));

        $this->assertNotNull($period);
        $this->assertSame('2026-07-01', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-07-31', $period['due']->format('Y-m-d'));
    }

    public function testIsReportOverdueIsFalseWhenIntervalNotConfigured(): void
    {
        $project = $this->createProject(null);

        $this->repository->expects($this->never())->method('findForPeriod');

        $this->assertFalse($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-06-01')));
    }

    public function testIsReportOverdueIsFalseBeforePeriodDueDate(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::WEEK);

        $this->repository->expects($this->never())->method('findForPeriod');

        // Wednesday mid-week — due is Sunday, not overdue yet
        $this->assertFalse($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-07-15')));
    }

    public function testIsReportOverdueIsTrueOnDueDateWithNoReport(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::WEEK);

        $this->repository->expects($this->once())
            ->method('findForPeriod')
            ->with($project, $this->callback(fn (\DateTimeImmutable $d) => $d->format('Y-m-d') === '2026-07-13'))
            ->willReturn(null);

        // Sunday 2026-07-19 = due date of the current week
        $this->assertTrue($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-07-19')));
    }

    public function testIsReportOverdueIsFalseWhenCurrentPeriodAlreadyReported(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::WEEK);

        $this->repository->expects($this->once())
            ->method('findForPeriod')
            ->willReturn(new ProjectProgressReport());

        $this->assertFalse($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-07-19')));
    }

    /**
     * Once today rolls into the next week, only that week is checked —
     * a missing report for the previous week is ignored.
     */
    public function testEarlierMissedPeriodsDoNotCauseOverdueInNextWeek(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::WEEK);

        // Monday 2026-07-20 starts a new week; previous week (07-13..07-19)
        // was never reported, but we only look up the new week's start.
        $this->repository->expects($this->never())->method('findForPeriod');

        // Mid next week, before due — not overdue for the new period
        $this->assertFalse($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-07-22')));
    }

    private function createProject(?ProjectProgressReportInterval $interval): Project
    {
        $project = new Project();
        $project->setProgressReportInterval($interval);

        return $project;
    }
}
