<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ProjectProgressReportStatus;
use App\Repository\ProjectProgressReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectProgressReportRepository::class)]
#[ORM\Table(name: 'project_progress_report')]
#[ORM\UniqueConstraint(name: 'uniq_progress_report_project_period', columns: ['project_id', 'period_start_date'])]
#[ORM\Index(columns: ['project_id'], name: 'idx_progress_report_project')]
#[UniqueEntity(fields: ['project', 'periodStartDate'], message: '本期（{{ value }}）的进度报告已存在，请勿重复填报。', ignoreNull: false)]
#[ORM\HasLifecycleCallbacks]
class ProjectProgressReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'progressReports')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: '所属项目不能为空')]
    private ?Project $project = null;

    // System-computed from Project::getCurrentReportingPeriod() - never edited directly by users.
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $periodStartDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $periodEndDate = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull(message: '完成百分比不能为空')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: '完成百分比必须在 {{ min }} 到 {{ max }} 之间')]
    private ?int $progressPercentage = null;

    #[ORM\Column(type: Types::STRING, enumType: ProjectProgressReportStatus::class)]
    private ProjectProgressReportStatus $statusTag = ProjectProgressReportStatus::NORMAL;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: '本期进度不能为空')]
    private ?string $currentProgressSummary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $nextPeriodPlan = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $issues = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $reportedBy = null;

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
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->periodStartDate !== null && $this->periodEndDate !== null
            ? $this->periodStartDate->format('Y-m-d').' ~ '.$this->periodEndDate->format('Y-m-d')
            : '进度报告';
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

    public function getPeriodStartDate(): ?\DateTimeImmutable
    {
        return $this->periodStartDate;
    }

    public function setPeriodStartDate(?\DateTimeImmutable $periodStartDate): self
    {
        $this->periodStartDate = $periodStartDate;

        return $this;
    }

    public function getPeriodEndDate(): ?\DateTimeImmutable
    {
        return $this->periodEndDate;
    }

    public function setPeriodEndDate(?\DateTimeImmutable $periodEndDate): self
    {
        $this->periodEndDate = $periodEndDate;

        return $this;
    }

    public function getProgressPercentage(): ?int
    {
        return $this->progressPercentage;
    }

    public function setProgressPercentage(?int $progressPercentage): self
    {
        $this->progressPercentage = $progressPercentage;

        return $this;
    }

    public function getStatusTag(): ProjectProgressReportStatus
    {
        return $this->statusTag;
    }

    public function setStatusTag(ProjectProgressReportStatus $statusTag): self
    {
        $this->statusTag = $statusTag;

        return $this;
    }

    public function getCurrentProgressSummary(): ?string
    {
        return $this->currentProgressSummary;
    }

    public function setCurrentProgressSummary(?string $currentProgressSummary): self
    {
        $this->currentProgressSummary = $currentProgressSummary;

        return $this;
    }

    public function getNextPeriodPlan(): ?string
    {
        return $this->nextPeriodPlan;
    }

    public function setNextPeriodPlan(?string $nextPeriodPlan): self
    {
        $this->nextPeriodPlan = $nextPeriodPlan;

        return $this;
    }

    public function getIssues(): ?string
    {
        return $this->issues;
    }

    public function setIssues(?string $issues): self
    {
        $this->issues = $issues;

        return $this;
    }

    public function getReportedBy(): ?User
    {
        return $this->reportedBy;
    }

    public function setReportedBy(?User $reportedBy): self
    {
        $this->reportedBy = $reportedBy;

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
