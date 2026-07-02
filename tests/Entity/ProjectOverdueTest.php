<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Project;
use App\Enum\ProjectStatus;
use PHPUnit\Framework\TestCase;

final class ProjectOverdueTest extends TestCase
{
    public function testIsOverdueWhenPastPlannedEndDate(): void
    {
        $project = $this->createProject('2026-01-01', ProjectStatus::IN_PROGRESS);

        self::assertTrue($project->isOverdue(new \DateTimeImmutable('2026-06-25')));
        self::assertSame(175, $project->getOverdueDays(new \DateTimeImmutable('2026-06-25')));
    }

    public function testIsNotOverdueOnPlannedEndDate(): void
    {
        $project = $this->createProject('2026-06-25', ProjectStatus::IN_PROGRESS);

        self::assertFalse($project->isOverdue(new \DateTimeImmutable('2026-06-25')));
        self::assertSame(0, $project->getOverdueDays(new \DateTimeImmutable('2026-06-25')));
    }

    public function testCompletedProjectIsNeverOverdue(): void
    {
        $project = $this->createProject('2026-01-01', ProjectStatus::COMPLETED);

        self::assertFalse($project->isOverdue(new \DateTimeImmutable('2026-06-25')));
    }

    public function testCancelledProjectIsNeverOverdue(): void
    {
        $project = $this->createProject('2026-01-01', ProjectStatus::CANCELLED);

        self::assertFalse($project->isOverdue(new \DateTimeImmutable('2026-06-25')));
    }

    private function createProject(string $plannedEndDate, ProjectStatus $status): Project
    {
        $project = new Project();
        $project->setPlannedEndDate(new \DateTimeImmutable($plannedEndDate));
        $project->setStatus($status);

        return $project;
    }
}
