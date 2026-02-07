<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Project;
use App\Service\ProjectDisplayService;

/**
 * Data Transfer Object for project card display (4.3.2.1)
 * Contains all fields needed for project homepage display
 */
class ProjectCardDTO
{
    // Basic core fields
    public readonly int $id;
    public readonly string $projectNumber;
    public readonly string $projectName;
    public readonly string $projectType;
    public readonly string $projectNature;

    // Progress fields
    public readonly string $currentStage;
    public readonly ?int $progressPercentage;
    public readonly int $overallProgress;
    public readonly string $statusLabel;
    public readonly string $statusBadgeClass;
    public readonly bool $isClosed;

    // Key personnel fields
    public readonly string $leaderName;
    public readonly string $leaderPhone; // Masked
    public readonly string $registrantName;

    // Core parameters
    public readonly string $budget;
    public readonly string $plannedTimeline;
    public readonly string $location;

    // Auxiliary fields (optional)
    public readonly string $fundingSource;
    public readonly ?string $contractorName;
    public readonly ?string $acceptanceResult;

    // System fields
    public readonly \DateTimeImmutable $createdAt;
    public readonly \DateTimeImmutable $updatedAt;

    public function __construct(
        Project $project,
        ProjectDisplayService $displayService
    ) {
        $this->id = $project->getId();
        $this->projectNumber = $project->getProjectNumber() ?? '';
        $this->projectName = $project->getProjectName() ?? '';
        $this->projectType = $displayService->getProjectTypeDisplay($project);
        $this->projectNature = $project->getProjectNature()?->label() ?? '';

        $this->currentStage = $displayService->getCurrentStageLabel($project);
        $this->progressPercentage = $displayService->getProgressPercentage($project);
        $this->overallProgress = $displayService->getOverallProgressPercentage($project);
        $this->statusLabel = $project->getStatus()->label();
        $this->statusBadgeClass = $displayService->getStatusBadgeClass($project);
        $this->isClosed = $displayService->isProjectClosed($project);

        $this->leaderName = $project->getLeaderName() ?? '';
        $this->leaderPhone = $displayService->maskPhoneNumber($project->getLeaderPhone() ?? '');
        $this->registrantName = $project->getRegistrantName() ?? '';

        $this->budget = $displayService->formatBudget($project->getBudget() ?? '0');
        $this->plannedTimeline = $displayService->formatPlannedTimeline($project);
        $this->location = $project->getProjectLocation() ?? '';

        $this->fundingSource = $project->getFundingSource()?->label() ?? '';
        $this->contractorName = $displayService->getContractorName($project);
        $this->acceptanceResult = $displayService->getAcceptanceResult($project);

        $this->createdAt = $project->getCreatedAt();
        $this->updatedAt = $project->getUpdatedAt();
    }
}
