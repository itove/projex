<?php

declare(strict_types=1);

namespace App\Service\Lifecycle;

use App\Entity\CompletionAcceptance;
use App\Entity\ConstructionImplementation;
use App\Entity\ConstructionPreparation;
use App\Entity\LifecycleStageInterface;
use App\Entity\PlanningDesign;
use App\Entity\PreliminaryDecision;
use App\Entity\Project;
use App\Entity\ProjectApproval;

/**
 * Single source of truth for the ordered list of project lifecycle stages.
 *
 * Every consumer that needs to know "how many stages are there", "what
 * order do they come in", "what's stage N called/iconed/routed", or "which
 * Project association backs stage N" should read from this registry
 * instead of hardcoding stage numbers/names.
 *
 * Reordering, renaming, or removing a stage is a one-place edit here.
 * Adding a genuinely new stage type still requires a new entity + migration
 * + CRUD controller (its fields are unique), but only one new entry here
 * on top of that - no other file needs to change.
 */
final class ProjectLifecycleStageRegistry
{
    /** @var list<LifecycleStageDefinition> */
    private readonly array $stages;

    public function __construct()
    {
        $this->stages = [
            new LifecycleStageDefinition(
                key: 'preliminary',
                name: '前期决策流程',
                progressLabel: '前期决策中',
                icon: 'fa-file-alt',
                route: 'admin_preliminary_decision',
                projectProperty: 'preliminaryDecision',
                requirementsHint: '需上传项目建议书、可行性研究报告等文档',
                entityAccessor: static fn (Project $project): ?LifecycleStageInterface => $project->getPreliminaryDecision(),
                infoAccessor: static fn (?LifecycleStageInterface $entity): ?string => $entity instanceof PreliminaryDecision
                    ? $entity->getOrganizingUnit()
                    : null,
            ),
            new LifecycleStageDefinition(
                key: 'approval',
                name: '立项流程',
                progressLabel: '立项中',
                icon: 'fa-check-square',
                route: 'admin_project_approval',
                projectProperty: 'projectApproval',
                requirementsHint: '需上传立项申请表、立项批复文件等',
                entityAccessor: static fn (Project $project): ?LifecycleStageInterface => $project->getProjectApproval(),
                infoAccessor: static fn (?LifecycleStageInterface $entity): ?string => $entity instanceof ProjectApproval
                    ? $entity->getApprovingAuthority()
                    : null,
            ),
            new LifecycleStageDefinition(
                key: 'planning',
                name: '规划设计流程',
                progressLabel: '规划与设计中',
                icon: 'fa-pencil-ruler',
                route: 'admin_planning_design',
                projectProperty: 'planningDesign',
                requirementsHint: '需上传规划审批文件、初步设计、施工图等',
                entityAccessor: static fn (Project $project): ?LifecycleStageInterface => $project->getPlanningDesign(),
                infoAccessor: static fn (?LifecycleStageInterface $entity): ?string => $entity instanceof PlanningDesign
                    ? $entity->getDesignUnit()
                    : null,
            ),
            new LifecycleStageDefinition(
                key: 'preparation',
                name: '施工准备流程',
                progressLabel: '施工准备中',
                icon: 'fa-tools',
                route: 'admin_construction_preparation',
                projectProperty: 'constructionPreparation',
                requirementsHint: '需上传招标文件、合同、施工许可证等',
                entityAccessor: static fn (Project $project): ?LifecycleStageInterface => $project->getConstructionPreparation(),
                infoAccessor: static fn (?LifecycleStageInterface $entity): ?string => $entity instanceof ConstructionPreparation
                    ? $entity->getConstructionUnit()
                    : null,
            ),
            new LifecycleStageDefinition(
                key: 'implementation',
                name: '施工实施流程',
                progressLabel: '施工实施中',
                icon: 'fa-hard-hat',
                route: 'admin_construction_implementation',
                projectProperty: 'constructionImplementation',
                requirementsHint: '需定期更新施工进度、上传现场照片和验收记录',
                entityAccessor: static fn (Project $project): ?LifecycleStageInterface => $project->getConstructionImplementation(),
                infoAccessor: static fn (?LifecycleStageInterface $entity): ?string => $entity instanceof ConstructionImplementation
                    && $entity->getCurrentProgress() !== null
                        ? $entity->getCurrentProgress() . '%'
                        : null,
            ),
            new LifecycleStageDefinition(
                key: 'acceptance',
                name: '竣工验收流程',
                progressLabel: '竣工验收中',
                icon: 'fa-clipboard-check',
                route: 'admin_completion_acceptance',
                projectProperty: 'completionAcceptance',
                requirementsHint: '需上传竣工验收报告、专项验收证明等',
                entityAccessor: static fn (Project $project): ?LifecycleStageInterface => $project->getCompletionAcceptance(),
                infoAccessor: static fn (?LifecycleStageInterface $entity): ?string => $entity instanceof CompletionAcceptance
                    ? ($entity->getAcceptanceDate() !== null ? '已验收' : '验收中')
                    : null,
            ),
            new LifecycleStageDefinition(
                key: 'settlement',
                name: '竣工结算流程',
                progressLabel: '竣工结算中',
                icon: 'fa-calculator',
                route: 'admin_settlement_accounts',
                projectProperty: 'settlementAccounts',
                requirementsHint: '需上传竣工结算书、决算报告、审计报告等',
                entityAccessor: static fn (Project $project): ?LifecycleStageInterface => $project->getSettlementAccounts(),
                infoAccessor: static fn (?LifecycleStageInterface $entity): ?string => null, // Simplified for now
            ),
        ];
    }

    /**
     * @return list<LifecycleStageDefinition>
     */
    public function all(): array
    {
        return $this->stages;
    }

    public function count(): int
    {
        return count($this->stages);
    }

    public function find(string $key): ?LifecycleStageDefinition
    {
        foreach ($this->stages as $stage) {
            if ($stage->key === $key) {
                return $stage;
            }
        }

        return null;
    }

    /**
     * 0-based position of a stage within the ordered list, or null if unknown.
     */
    public function indexOf(string $key): ?int
    {
        foreach ($this->stages as $index => $stage) {
            if ($stage->key === $key) {
                return $index;
            }
        }

        return null;
    }
}
