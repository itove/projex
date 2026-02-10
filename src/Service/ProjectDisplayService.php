<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Project;

/**
 * Service to calculate derived display fields for projects
 * according to section 4.3.2 of the design document.
 */
class ProjectDisplayService
{
    /**
     * Get the current lifecycle stage label for a project
     */
    public function getCurrentStageLabel(Project $project): string
    {
        if ($project->getSettlementAccounts() !== null) {
            return '竣工结算中';
        }
        if ($project->getCompletionAcceptance() !== null) {
            return '竣工验收中';
        }
        if ($project->getConstructionImplementation() !== null) {
            return '施工实施中';
        }
        if ($project->getConstructionPreparation() !== null) {
            return '施工准备中';
        }
        if ($project->getPlanningDesign() !== null) {
            return '规划与设计中';
        }
        if ($project->getProjectApproval() !== null) {
            return '立项中';
        }
        if ($project->getPreliminaryDecision() !== null) {
            return '前期决策中';
        }

        return '待前期决策';
    }

    /**
     * Get progress percentage (only for construction implementation stage)
     */
    public function getProgressPercentage(Project $project): ?int
    {
        $implementation = $project->getConstructionImplementation();
        if ($implementation === null) {
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
        $preparation = $project->getConstructionPreparation();
        if ($preparation === null) {
            return null;
        }

        return $preparation->getConstructionUnit();
    }

    /**
     * Get acceptance result from completion acceptance stage
     */
    public function getAcceptanceResult(Project $project): ?string
    {
        $acceptance = $project->getCompletionAcceptance();
        if ($acceptance === null) {
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
     * Calculate lifecycle stage progress (1-7, based on which stages are completed)
     */
    public function getLifecycleStageNumber(Project $project): int
    {
        if ($project->getSettlementAccounts() !== null) {
            return 7;
        }
        if ($project->getCompletionAcceptance() !== null) {
            return 6;
        }
        if ($project->getConstructionImplementation() !== null) {
            return 5;
        }
        if ($project->getConstructionPreparation() !== null) {
            return 4;
        }
        if ($project->getPlanningDesign() !== null) {
            return 3;
        }
        if ($project->getProjectApproval() !== null) {
            return 2;
        }
        if ($project->getPreliminaryDecision() !== null) {
            return 1;
        }

        return 0;
    }

    /**
     * Calculate overall progress percentage (based on lifecycle stage)
     */
    public function getOverallProgressPercentage(Project $project): int
    {
        $stage = $this->getLifecycleStageNumber($project);

        // Each stage is roughly 14.3% (100 / 7)
        $baseProgress = (int) ($stage * 14.3);

        // If in construction implementation, add detailed progress
        if ($stage === 5) {
            $implementationProgress = $this->getProgressPercentage($project);
            if ($implementationProgress !== null) {
                // Add up to 14% based on implementation progress
                $baseProgress += (int) ($implementationProgress * 0.143);
            }
        }

        return min(100, $baseProgress);
    }

    /**
     * Get lifecycle stages information for project detail page
     */
    public function getLifecycleStages(Project $project): array
    {
        return [
            [
                'number' => 1,
                'name' => '前期决策流程',
                'icon' => 'fa-file-alt',
                'entity' => $project->getPreliminaryDecision(),
                'route' => 'admin_preliminary_decision',
                'status' => $this->getStageStatus($project->getPreliminaryDecision()),
                'info' => $this->getStageInfo($project->getPreliminaryDecision(), '决策单位'),
            ],
            [
                'number' => 2,
                'name' => '立项流程',
                'icon' => 'fa-check-square',
                'entity' => $project->getProjectApproval(),
                'route' => 'admin_project_approval',
                'status' => $this->getStageStatus($project->getProjectApproval()),
                'info' => $this->getStageInfo($project->getProjectApproval(), '审批部门'),
            ],
            [
                'number' => 3,
                'name' => '规划设计流程',
                'icon' => 'fa-pencil-ruler',
                'entity' => $project->getPlanningDesign(),
                'route' => 'admin_planning_design',
                'status' => $this->getStageStatus($project->getPlanningDesign()),
                'info' => $this->getStageInfo($project->getPlanningDesign(), '设计单位'),
            ],
            [
                'number' => 4,
                'name' => '施工准备流程',
                'icon' => 'fa-tools',
                'entity' => $project->getConstructionPreparation(),
                'route' => 'admin_construction_preparation',
                'status' => $this->getStageStatus($project->getConstructionPreparation()),
                'info' => $this->getStageInfo($project->getConstructionPreparation(), '施工单位'),
            ],
            [
                'number' => 5,
                'name' => '施工实施流程',
                'icon' => 'fa-hard-hat',
                'entity' => $project->getConstructionImplementation(),
                'route' => 'admin_construction_implementation',
                'status' => $this->getStageStatus($project->getConstructionImplementation()),
                'info' => $this->getStageInfo($project->getConstructionImplementation(), '实施进度'),
                'progress' => $this->getProgressPercentage($project),
            ],
            [
                'number' => 6,
                'name' => '竣工验收流程',
                'icon' => 'fa-clipboard-check',
                'entity' => $project->getCompletionAcceptance(),
                'route' => 'admin_completion_acceptance',
                'status' => $this->getStageStatus($project->getCompletionAcceptance()),
                'info' => $this->getStageInfo($project->getCompletionAcceptance(), '验收结果'),
            ],
            [
                'number' => 7,
                'name' => '竣工结算流程',
                'icon' => 'fa-calculator',
                'entity' => $project->getSettlementAccounts(),
                'route' => 'admin_settlement_accounts',
                'status' => $this->getStageStatus($project->getSettlementAccounts()),
                'info' => $this->getStageInfo($project->getSettlementAccounts(), '结算金额'),
            ],
        ];
    }

    /**
     * Get stage status (completed, in_progress, not_started)
     */
    private function getStageStatus(?object $stageEntity): string
    {
        if ($stageEntity === null) {
            return 'not_started';
        }

        // Check if stage has completion date or is fully filled
        if (method_exists($stageEntity, 'getCompletionDate')) {
            return $stageEntity->getCompletionDate() !== null ? 'completed' : 'in_progress';
        }

        if (method_exists($stageEntity, 'getAcceptanceDate')) {
            return $stageEntity->getAcceptanceDate() !== null ? 'completed' : 'in_progress';
        }

        // For stages without specific completion date, consider them in progress if entity exists
        return 'in_progress';
    }

    /**
     * Get stage summary information
     */
    private function getStageInfo(?object $stageEntity, string $type): ?string
    {
        if ($stageEntity === null) {
            return null;
        }

        return match ($type) {
            '决策单位' => method_exists($stageEntity, 'getOrganizingUnit')
                ? $stageEntity->getOrganizingUnit()
                : null,
            '审批部门' => method_exists($stageEntity, 'getApprovalDepartment')
                ? $stageEntity->getApprovalDepartment()
                : null,
            '设计单位' => method_exists($stageEntity, 'getDesignUnit')
                ? $stageEntity->getDesignUnit()
                : null,
            '施工单位' => method_exists($stageEntity, 'getConstructionUnit')
                ? $stageEntity->getConstructionUnit()
                : null,
            '实施进度' => method_exists($stageEntity, 'getCurrentProgress') && $stageEntity->getCurrentProgress() !== null
                ? $stageEntity->getCurrentProgress() . '%'
                : null,
            '验收结果' => method_exists($stageEntity, 'getAcceptanceDate') && $stageEntity->getAcceptanceDate() !== null
                ? '已验收'
                : '验收中',
            '结算金额' => null, // Simplified for now
            default => null,
        };
    }

    /**
     * Count files for a stage entity
     */
    public function getStageFileCount(?object $stageEntity): int
    {
        if ($stageEntity === null || !method_exists($stageEntity, 'getFiles')) {
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
            'totalStages' => 7,
            'currentStage' => $this->getCurrentStageLabel($project),
            'currentProgress' => $this->getProgressPercentage($project),
            'plannedEndDate' => $project->getPlannedEndDate(),
            'totalFiles' => $totalFiles,
            'paymentProgress' => 50,
            'stageProgress' => 80,
        ];
    }
}
