<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ProjectLifecycleStage;
use App\Enum\ProjectTaskPriority;
use App\Enum\ProjectTaskStatus;
use App\Repository\ProjectTaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectTaskRepository::class)]
#[ORM\Table(name: 'project_task')]
#[ORM\Index(columns: ['project_id'], name: 'idx_project_task_project')]
#[ORM\Index(columns: ['status'], name: 'idx_project_task_status')]
#[ORM\Index(columns: ['assignee_id'], name: 'idx_project_task_assignee')]
#[ORM\Index(columns: ['due_date'], name: 'idx_project_task_due_date')]
#[ORM\Index(columns: ['start_date'], name: 'idx_project_task_start_date')]
#[ORM\Index(columns: ['end_date'], name: 'idx_project_task_end_date')]
#[ORM\Index(columns: ['lifecycle_stage'], name: 'idx_project_task_lifecycle_stage')]
#[ORM\HasLifecycleCallbacks]
class ProjectTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: '所属项目不能为空')]
    private ?Project $project = null;

    #[ORM\Column(type: Types::STRING, enumType: ProjectLifecycleStage::class, nullable: true)]
    private ?ProjectLifecycleStage $lifecycleStage = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: '任务名称不能为空')]
    #[Assert\Length(max: 255, maxMessage: '任务名称不能超过 {{ limit }} 个字符')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: '责任单位不能超过 {{ limit }} 个字符')]
    private ?string $responsibleUnit = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: '配合单位不能超过 {{ limit }} 个字符')]
    private ?string $cooperatingUnit = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'startDate',
        message: '结束时间不能早于开始时间'
    )]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $durationDays = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $progressText = null;

    #[ORM\Column(type: Types::STRING, enumType: ProjectTaskStatus::class)]
    private ProjectTaskStatus $status = ProjectTaskStatus::PENDING;

    #[ORM\Column(type: Types::STRING, enumType: ProjectTaskPriority::class)]
    private ProjectTaskPriority $priority = ProjectTaskPriority::MEDIUM;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $assignee = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->syncDerivedFields();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->syncDerivedFields();
    }

    public function syncDerivedFields(): void
    {
        if ($this->startDate !== null && $this->endDate !== null) {
            $this->durationDays = $this->startDate->diff($this->endDate)->days + 1;
        } else {
            $this->durationDays = null;
        }

        if ($this->status === ProjectTaskStatus::DONE) {
            $this->completedAt ??= new \DateTimeImmutable();
        } else {
            $this->completedAt = null;
        }
    }

    public function isOverdue(): bool
    {
        if (!$this->status->isOpen() || $this->dueDate === null) {
            return false;
        }

        return $this->dueDate < new \DateTimeImmutable('today');
    }

    public function __toString(): string
    {
        return $this->title ?? '新任务';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getLifecycleStage(): ?ProjectLifecycleStage
    {
        return $this->lifecycleStage;
    }

    public function setLifecycleStage(?ProjectLifecycleStage $lifecycleStage): self
    {
        $this->lifecycleStage = $lifecycleStage;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getResponsibleUnit(): ?string
    {
        return $this->responsibleUnit;
    }

    public function setResponsibleUnit(?string $responsibleUnit): self
    {
        $this->responsibleUnit = $responsibleUnit;

        return $this;
    }

    public function getCooperatingUnit(): ?string
    {
        return $this->cooperatingUnit;
    }

    public function setCooperatingUnit(?string $cooperatingUnit): self
    {
        $this->cooperatingUnit = $cooperatingUnit;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDurationDays(): ?int
    {
        return $this->durationDays;
    }

    public function getProgressText(): ?string
    {
        return $this->progressText;
    }

    public function setProgressText(?string $progressText): self
    {
        $this->progressText = $progressText;

        return $this;
    }

    public function getStatus(): ProjectTaskStatus
    {
        return $this->status;
    }

    public function setStatus(ProjectTaskStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPriority(): ProjectTaskPriority
    {
        return $this->priority;
    }

    public function setPriority(ProjectTaskPriority $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getAssignee(): ?User
    {
        return $this->assignee;
    }

    public function setAssignee(?User $assignee): self
    {
        $this->assignee = $assignee;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
