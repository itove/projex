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
use App\Entity\SettlementAccounts;
use App\Enum\ProjectLifecycleStage;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Single source of truth for the ordered list of project lifecycle stages.
 *
 * Every consumer that needs to know "how many stages are there", "what
 * order do they come in", "what's stage N called/iconed/routed", or "which
 * entity backs stage N" should read from this registry instead of
 * hardcoding stage numbers/names.
 *
 * Stage entities are linked to Project unidirectionally (each stage entity
 * owns a `project` reference; Project itself has no matching property), so
 * adding, removing, or reordering a stage is a one-place edit here plus a
 * new entity/migration/CRUD controller for a genuinely new stage - nothing
 * on Project ever needs to change.
 */
final class ProjectLifecycleStageRegistry
{
    /** @var list<LifecycleStageDefinition> */
    private readonly array $stages;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->stages = [
            new LifecycleStageDefinition(
                key: 'preliminary',
                name: '前期决策流程',
                progressLabel: '前期决策中',
                icon: 'fa-file-alt',
                route: 'admin_preliminary_decision',
                entityClass: PreliminaryDecision::class,
                requirementsHint: '需上传项目建议书、可行性研究报告等文档',
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
                entityClass: ProjectApproval::class,
                requirementsHint: '需上传立项申请表、立项批复文件等',
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
                entityClass: PlanningDesign::class,
                requirementsHint: '需上传规划审批文件、初步设计、施工图等',
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
                entityClass: ConstructionPreparation::class,
                requirementsHint: '需上传招标文件、合同、施工许可证等',
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
                entityClass: ConstructionImplementation::class,
                requirementsHint: '需定期更新施工进度、上传现场照片和验收记录',
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
                entityClass: CompletionAcceptance::class,
                requirementsHint: '需上传竣工验收报告、专项验收证明等',
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
                entityClass: SettlementAccounts::class,
                requirementsHint: '需上传竣工结算书、决算报告、审计报告等',
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

    /**
     * @param class-string $entityClass
     */
    public function findByEntityClass(string $entityClass): ?LifecycleStageDefinition
    {
        foreach ($this->stages as $stage) {
            if ($stage->entityClass === $entityClass) {
                return $stage;
            }
        }

        return null;
    }

    /**
     * @param class-string $entityClass
     */
    public function stageEnumForEntityClass(string $entityClass): ?ProjectLifecycleStage
    {
        $definition = $this->findByEntityClass($entityClass);

        return $definition !== null ? ProjectLifecycleStage::tryFromKey($definition->key) : null;
    }

    /**
     * Fetch a stage's entity for a given project. Project has no direct
     * association to stage entities - each stage entity owns a `project`
     * reference instead - so this is a lookup rather than a property read.
     * An unpersisted project can't have any stage rows yet.
     */
    public function getEntity(Project $project, LifecycleStageDefinition $definition): ?LifecycleStageInterface
    {
        if ($project->getId() === null) {
            return null;
        }

        /** @var LifecycleStageInterface|null $entity */
        $entity = $this->entityManager->getRepository($definition->entityClass)->findOneBy(['project' => $project]);

        return $entity;
    }

    public function findEntity(Project $project, string $key): ?LifecycleStageInterface
    {
        $definition = $this->find($key);

        return $definition !== null ? $this->getEntity($project, $definition) : null;
    }
}
