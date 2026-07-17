<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Project;
use App\Entity\ProjectProgressReport;
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
        $project = $this->createProject(null, '2026-01-01');

        $this->assertNull($this->service->getCurrentPeriodRange($project, new \DateTimeImmutable('2026-03-01')));
    }

    public function testGetCurrentPeriodRangeComputesFromAnchorAndInterval(): void
    {
        $project = $this->createProject(7, '2026-01-01');

        // 20 days after anchor = 2 full 7-day intervals completed (periods
        // [01-01,01-08) and [01-08,01-15)); the most recently *completed*
        // one - [01-08,01-15) - is the one currently owed a report.
        $period = $this->service->getCurrentPeriodRange($project, new \DateTimeImmutable('2026-01-21'));

        $this->assertNotNull($period);
        $this->assertSame('2026-01-08', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-01-15', $period['due']->format('Y-m-d'));
    }

    public function testIsReportOverdueIsFalseWhenIntervalNotConfigured(): void
    {
        $project = $this->createProject(null, '2026-01-01');

        $this->repository->expects($this->never())->method('findForPeriod');

        $this->assertFalse($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-06-01')));
    }

    public function testIsReportOverdueIsFalseBeforeCurrentPeriodDueDate(): void
    {
        $project = $this->createProject(7, '2026-01-01');

        $this->repository->expects($this->never())->method('findForPeriod');

        // Still within the first 7-day period (due 2026-01-08) - not overdue yet.
        $this->assertFalse($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-01-05')));
    }

    public function testIsReportOverdueIsTrueWhenCurrentPeriodPastDueWithNoReport(): void
    {
        $project = $this->createProject(7, '2026-01-01');

        $this->repository->expects($this->once())
            ->method('findForPeriod')
            ->with($project, $this->callback(fn (\DateTimeImmutable $d) => $d->format('Y-m-d') === '2026-01-01'))
            ->willReturn(null);

        $this->assertTrue($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-01-10')));
    }

    public function testIsReportOverdueIsFalseWhenCurrentPeriodAlreadyReported(): void
    {
        $project = $this->createProject(7, '2026-01-01');

        $this->repository->expects($this->once())
            ->method('findForPeriod')
            ->willReturn(new ProjectProgressReport());

        $this->assertFalse($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-01-10')));
    }

    /**
     * Only the *current* (most recently completed) period is ever checked -
     * a report missing for an earlier period must not make a project that
     * is current on its filing appear overdue.
     */
    public function testEarlierMissedPeriodsDoNotCauseOverdueOnceCurrentPeriodIsFiled(): void
    {
        $project = $this->createProject(7, '2026-01-01');

        // As of 2026-01-22, three 7-day intervals have completed; the
        // period currently owed a report is [01-15, 01-22) - period
        // [01-01, 01-08) was never reported, but that's irrelevant/ignored.
        $this->repository->expects($this->once())
            ->method('findForPeriod')
            ->with($project, $this->callback(fn (\DateTimeImmutable $d) => $d->format('Y-m-d') === '2026-01-15'))
            ->willReturn(new ProjectProgressReport());

        $this->assertFalse($this->service->isReportOverdue($project, new \DateTimeImmutable('2026-01-22')));
    }

    private function createProject(?int $intervalDays, string $plannedStartDate): Project
    {
        $project = new Project();
        $project->setProgressReportIntervalDays($intervalDays);
        $project->setPlannedStartDate(new \DateTimeImmutable($plannedStartDate));

        return $project;
    }
}
