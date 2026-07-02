<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CompletionAcceptance;
use App\Entity\ConstructionImplementation;
use App\Entity\ConstructionPreparation;
use App\Entity\LifecycleStageInterface;
use App\Entity\Project;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use App\Service\Lifecycle\StageAttachmentComplianceService;

/**
 * Service to calculate derived display fields for projects
 * according to section 4.3.2 of the design document.
 */
class ProjectDisplayService
{
    public function __construct(
        private readonly ProjectLifecycleStageRegistry $stageRegistry,
        private readonly StageAttachmentComplianceService $attachmentComplianceService,
    ) {
    }

    /**
     * Get the current lifecycle stage label for a project
     */
    public function getCurrentStageLabel(Project $project): string
    {
        $activeStage = $this->findLastActiveStageIndex($project);
        if ($activeStage === null) {
            return '待前期决策';
        }

        return $this->stageRegistry->all()[$activeStage]->progressLabel;
    }

    /**
     * Get progress percentage (only for construction implementation stage)
     */
    public function getProgressPercentage(Project $project): ?int
    {
        $implementation = $this->stageRegistry->findEntity($project, 'implementation');
        if (!$implementation instanceof ConstructionImplementation) {
            return null;
        }

        return $implementation->getCurrentProgress();
    }

    /**
     * Get status badge class for color coding
     */
    public function getStatusBadgeClass(Project $project): string
    {
        return match ($project->getStatus()) {
            \App\Enum\ProjectStatus::DRAFT => 'badge-secondary',
            \App\Enum\ProjectStatus::REGISTERED => 'badge-info',
            \App\Enum\ProjectStatus::IN_PRELIMINARY_DECISION => 'badge-primary',
            \App\Enum\ProjectStatus::PRELIMINARY_APPROVED => 'badge-success',
            \App\Enum\ProjectStatus::IN_PROGRESS => 'badge-warning',
            \App\Enum\ProjectStatus::COMPLETED => 'badge-success',
            \App\Enum\ProjectStatus::CANCELLED => 'badge-danger',
        };
    }

    /**
     * Check if project is closed (completed or cancelled)
     */
    public function isProjectClosed(Project $project): bool
    {
        return in_array(
            $project->getStatus(),
            [
                \App\Enum\ProjectStatus::COMPLETED,
                \App\Enum\ProjectStatus::CANCELLED
            ],
            true
        );
    }

    /**
     * Mask phone number for privacy (show first 3 and last 4 digits)
     * Example: 13812345678 -> 138****5678
     */
    public function maskPhoneNumber(string $phone): string
    {
        if (strlen($phone) !== 11) {
            return $phone;
        }

        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }

    /**
     * Get project type display string (including subtype if available)
     */
    public function getProjectTypeDisplay(Project $project): string
    {
        $type = $project->getProjectType()?->getName() ?? '';
        $subtype = $project->getProjectSubtype()?->getName();

        if ($subtype !== null) {
            return $type . ' / ' . $subtype;
        }

        return $type;
    }

    /**
     * Get contractor name from construction preparation stage
     */
    public function getContractorName(Project $project): ?string
    {
        $preparation = $this->stageRegistry->findEntity($project, 'preparation');
        if (!$preparation instanceof ConstructionPreparation) {
            return null;
        }

        return $preparation->getConstructionUnit();
    }

    /**
     * Get acceptance result from completion acceptance stage
     */
    public function getAcceptanceResult(Project $project): ?string
    {
        $acceptance = $this->stageRegistry->findEntity($project, 'acceptance');
        if (!$acceptance instanceof CompletionAcceptance) {
            return null;
        }

        // Return a simple indicator since CompletionAcceptance exists
        return $acceptance->getAcceptanceDate() !== null ? '已验收' : '验收中';
    }

    /**
     * Format budget for display (in 万元, 2 decimal places)
     */
    public function formatBudget(string $budget): string
    {
        $budgetFloat = (float) $budget;
        $budgetWanYuan = $budgetFloat / 10000;

        return number_format($budgetWanYuan, 2, '.', ',') . ' 万元';
    }

    /**
     * Format date range for planned timeline
     */
    public function formatPlannedTimeline(Project $project): string
    {
        $start = $project->getPlannedStartDate();
        $end = $project->getPlannedEndDate();

        if ($start === null || $end === null) {
            return '';
        }

        return $start->format('Y-m-d') . ' ~ ' . $end->format('Y-m-d');
    }

    /**
     * Calculate lifecycle stage progress (1..N, based on which stages are completed),
     * where N is the number of stages known to the lifecycle stage registry.
     */
    public function getLifecycleStageNumber(Project $project): int
    {
        $activeStage = $this->findLastActiveStageIndex($project);

        return $activeStage === null ? 0 : $activeStage + 1;
    }

    /**
     * Calculate overall progress percentage (based on lifecycle stage)
     */
    public function getOverallProgressPercentage(Project $project): int
    {
        $totalStages = $this->stageRegistry->count();
        $stageNumber = $this->getLifecycleStageNumber($project);
        $stagePercentage = 100 / $totalStages;
        $baseProgress = (int) ($stageNumber * $stagePercentage);

        // If currently in the construction implementation stage, add detailed progress
        $currentDefinition = $stageNumber > 0 ? $this->stageRegistry->all()[$stageNumber - 1] : null;
        if ($currentDefinition !== null && $currentDefinition->key === 'implementation') {
            $implementationProgress = $this->getProgressPercentage($project);
            if ($implementationProgress !== null) {
                $baseProgress += (int) ($implementationProgress * $stagePercentage / 100);
            }
        }

        return min(100, $baseProgress);
    }

    /**
     * Get lifecycle stages information for project detail page
     */
    public function getLifecycleStages(Project $project): array
    {
        $stages = [];

        foreach ($this->stageRegistry->all() as $index => $definition) {
            $entity = $this->stageRegistry->getEntity($project, $definition);

            $stage = [
                'key' => $definition->key,
                'number' => $index + 1,
                'name' => $definition->name,
                'icon' => $definition->icon,
                'entity' => $entity,
                'route' => $definition->route,
                'status' => $this->getStageStatus($entity),
                'info' => $definition->getInfo($entity),
                'requirementsHint' => $definition->requirementsHint,
                'attachments' => $this->attachmentComplianceService->buildChecklist($definition, $entity),
                'attachmentsCompliant' => $this->attachmentComplianceService->isCompliant($definition, $entity),
                'missingRequiredAttachments' => $this->attachmentComplianceService->countMissingRequired($definition, $entity),
            ];

            if ($definition->key === 'implementation') {
                $stage['progress'] = $this->getProgressPercentage($project);
            }

            $stages[] = $stage;
        }

        return $stages;
    }

    /**
     * Get stage status (completed, in_progress, not_started)
     */
    private function getStageStatus(?LifecycleStageInterface $stageEntity): string
    {
        if ($stageEntity === null) {
            return 'not_started';
        }

        return $stageEntity->isComplete() ? 'completed' : 'in_progress';
    }

    /**
     * Count files for a stage entity
     */
    public function getStageFileCount(?LifecycleStageInterface $stageEntity): int
    {
        if ($stageEntity === null) {
            return 0;
        }

        return $stageEntity->getFiles()->count();
    }

    /**
     * Get stage summary for detail page header
     */
    public function getProjectSummary(Project $project): array
    {
        $completedStages = 0;
        $totalFiles = 0;

        foreach ($this->getLifecycleStages($project) as $stage) {
            if ($stage['status'] === 'completed') {
                $completedStages++;
            }
            $totalFiles += $this->getStageFileCount($stage['entity']);
        }

        return [
            'overallProgress' => $this->getOverallProgressPercentage($project),
            'completedStages' => $completedStages,
            'totalStages' => $this->stageRegistry->count(),
            'currentStage' => $this->getCurrentStageLabel($project),
            'currentProgress' => $this->getProgressPercentage($project),
            'plannedEndDate' => $project->getPlannedEndDate(),
            'totalFiles' => $totalFiles,
            'paymentProgress' => 50,
        ];
    }

    /**
     * Find the 0-based index of the furthest stage that has been started
     * (i.e. has a non-null stage entity), or null if no stage has started yet.
     */
    private function findLastActiveStageIndex(Project $project): ?int
    {
        $stages = $this->stageRegistry->all();
        for ($i = count($stages) - 1; $i >= 0; $i--) {
            if ($this->stageRegistry->getEntity($project, $stages[$i]) !== null) {
                return $i;
            }
        }

        return null;
    }
}
