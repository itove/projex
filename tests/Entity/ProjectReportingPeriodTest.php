<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Project;
use PHPUnit\Framework\TestCase;

final class ProjectReportingPeriodTest extends TestCase
{
    public function testNullWhenIntervalNotConfigured(): void
    {
        $project = $this->createProject(null, '2026-01-01');

        $this->assertNull($project->getCurrentReportingPeriod(new \DateTimeImmutable('2026-06-01')));
    }

    public function testMostRecentlyCompletedIntervalIsReturned(): void
    {
        $project = $this->createProject(7, '2026-01-01');

        // 20 days after anchor = 2 full 7-day intervals completed
        // ([01-01,01-08) and [01-08,01-15)) - the most recently completed
        // one, [01-08,01-15), is what's currently owed a report.
        $period = $project->getCurrentReportingPeriod(new \DateTimeImmutable('2026-01-21'));

        $this->assertNotNull($period);
        $this->assertSame('2026-01-08', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-01-15', $period['due']->format('Y-m-d'));
    }

    /**
     * Regression test: `plannedStartDate` is hydrated by Doctrine at
     * midnight in PHP's *default* timezone (UTC in this app, since it's
     * never explicitly changed - see the `date_default_timezone_get()`
     * check), while `getCurrentReportingPeriod()` builds "today" in
     * Asia/Shanghai when no override is passed. Diffing across mismatched
     * timezones used to lose up to 8 hours, which could truncate the
     * elapsed-day count by a full day and silently shift the whole period
     * back by one entire interval whenever "today" landed exactly on an
     * interval boundary (a common case for a weekly cadence).
     */
    public function testAnchorAndTodayInDifferentTimezonesStillAgreeOnTheDayCount(): void
    {
        $project = $this->createProject(7, '2026-07-02', new \DateTimeZone('UTC'));

        // Filing "today" (2026-07-16, Shanghai time) should reflect the
        // period that just ended today - [07-09, 07-16) - not jump back an
        // extra week to [07-02, 07-09).
        $period = $project->getCurrentReportingPeriod(new \DateTimeImmutable('2026-07-16', new \DateTimeZone('Asia/Shanghai')));

        $this->assertNotNull($period);
        $this->assertSame('2026-07-09', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-07-16', $period['due']->format('Y-m-d'));
    }

    public function testStillWithinFirstIntervalReturnsThatIntervalNotYetDue(): void
    {
        $project = $this->createProject(7, '2026-01-01');

        $period = $project->getCurrentReportingPeriod(new \DateTimeImmutable('2026-01-04'));

        $this->assertNotNull($period);
        $this->assertSame('2026-01-01', $period['start']->format('Y-m-d'));
        $this->assertSame('2026-01-08', $period['due']->format('Y-m-d'));
    }

    private function createProject(?int $intervalDays, string $plannedStartDate, ?\DateTimeZone $timezone = null): Project
    {
        $project = new Project();
        $project->setProgressReportIntervalDays($intervalDays);
        $project->setPlannedStartDate(new \DateTimeImmutable($plannedStartDate, $timezone));

        return $project;
    }
}
