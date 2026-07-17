<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Project;
use App\Enum\ProjectProgressReportInterval;
use PHPUnit\Framework\TestCase;

final class ProjectReportingPeriodTest extends TestCase
{
    public function testNullWhenIntervalNotConfigured(): void
    {
        $project = $this->createProject(null);

        $this->assertNull($project->getCurrentReportingPeriod(new \DateTimeImmutable('2026-06-01')));
    }

    public function testWeeklyPeriodIsMondayThroughSundayContainingToday(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::WEEK);

        // Thursday 2026-07-16 → week Mon 2026-07-13 … Sun 2026-07-19
        $period = $project->getCurrentReportingPeriod(new \DateTimeImmutable('2026-07-16', new \DateTimeZone('Asia/Shanghai')));

        $this->assertNotNull($period);
        $this->assertSame('2026-07-13', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-07-19', $period['due']->format('Y-m-d'));
    }

    public function testMonthlyPeriodIsFirstThroughLastDayOfMonth(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::MONTH);

        $period = $project->getCurrentReportingPeriod(new \DateTimeImmutable('2026-07-16', new \DateTimeZone('Asia/Shanghai')));

        $this->assertNotNull($period);
        $this->assertSame('2026-07-01', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-07-31', $period['due']->format('Y-m-d'));
    }

    public function testSubmitAlwaysUsesInProgressPeriodNotPreviousWeek(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::WEEK);

        // Still mid-week — current period is this week, not last week
        $period = $project->getCurrentReportingPeriod(new \DateTimeImmutable('2026-07-15'));

        $this->assertNotNull($period);
        $this->assertSame('2026-07-13', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-07-19', $period['due']->format('Y-m-d'));
    }

    /**
     * Calendar boundaries must not shift when “today” is built in
     * Asia/Shanghai while other dates might be UTC-hydrated.
     */
    public function testTimezoneNormalizationDoesNotShiftCalendarWeek(): void
    {
        $project = $this->createProject(ProjectProgressReportInterval::WEEK);

        $period = $project->getCurrentReportingPeriod(
            new \DateTimeImmutable('2026-07-16 00:00:00', new \DateTimeZone('Asia/Shanghai'))
        );

        $this->assertNotNull($period);
        $this->assertSame('2026-07-13', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-07-19', $period['due']->format('Y-m-d'));
    }

    private function createProject(?ProjectProgressReportInterval $interval): Project
    {
        $project = new Project();
        $project->setProgressReportInterval($interval);

        return $project;
    }
}
