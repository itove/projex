<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\FundingSource;
use App\Enum\ProjectNature;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'project')]
#[ORM\Index(columns: ['project_number'], name: 'idx_project_number')]
#[ORM\Index(columns: ['status'], name: 'idx_status')]
#[ORM\Index(columns: ['created_at'], name: 'idx_created_at')]
#[ORM\HasLifecycleCallbacks]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    // Project Basic Info
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: '项目名称不能为空')]
    #[Assert\Length(max: 255, maxMessage: '项目名称不能超过 {{ limit }} 个字符')]
    private ?string $projectName = null;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true, nullable: true)]
    private ?string $projectNumber = null;

    #[ORM\ManyToOne(targetEntity: ProjectType::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull(message: '项目类型不能为空')]
    private ?ProjectType $projectType = null;

    #[ORM\ManyToOne(targetEntity: ProjectSubtype::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ProjectSubtype $projectSubtype = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: '项目行业不能为空')]
    #[Assert\Length(max: 100, maxMessage: '项目行业不能超过 {{ limit }} 个字符')]
    private ?string $projectIndustry = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: '项目地点不能为空')]
    #[Assert\Length(max: 255, maxMessage: '项目地点不能超过 {{ limit }} 个字符')]
    private ?string $projectLocation = null;

    #[ORM\Column(type: Types::STRING, enumType: ProjectNature::class)]
    #[Assert\NotNull(message: '项目性质不能为空')]
    private ?ProjectNature $projectNature = null;

    // Project Leader Info
    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: '负责人姓名不能为空')]
    #[Assert\Length(max: 100, maxMessage: '负责人姓名不能超过 {{ limit }} 个字符')]
    private ?string $leaderName = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Assert\NotBlank(message: '负责人电话不能为空')]
    #[Assert\Regex(
        pattern: '/^1[3-9]\d{9}$/',
        message: '请输入有效的手机号码'
    )]
    private ?string $leaderPhone = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Email(message: '请输入有效的邮箱地址')]
    #[Assert\Length(max: 255, maxMessage: '邮箱地址不能超过 {{ limit }} 个字符')]
    private ?string $leaderEmail = null;

    // Project Parameters
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    #[Assert\NotBlank(message: '项目预算不能为空')]
    #[Assert\Positive(message: '项目预算必须大于 0')]
    private ?string $budget = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: '计划开始日期不能为空')]
    private ?\DateTimeImmutable $plannedStartDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: '计划结束日期不能为空')]
    #[Assert\GreaterThan(
        propertyPath: 'plannedStartDate',
        message: '计划结束日期必须晚于开始日期'
    )]
    private ?\DateTimeImmutable $plannedEndDate = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: '项目目的不能为空')]
    private ?string $purpose = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: '项目规模不能为空')]
    private ?string $scale = null;

    #[ORM\Column(type: Types::STRING, enumType: FundingSource::class)]
    #[Assert\NotNull(message: '资金来源不能为空')]
    private ?FundingSource $fundingSource = null;

    // Registrant Info
    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: '登记人姓名不能为空')]
    #[Assert\Length(max: 100, maxMessage: '登记人姓名不能超过 {{ limit }} 个字符')]
    private ?string $registrantName = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: '登记人单位不能为空')]
    #[Assert\Length(max: 255, maxMessage: '登记人单位不能超过 {{ limit }} 个字符')]
    private ?string $registrantOrganization = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Assert\NotBlank(message: '登记人电话不能为空')]
    #[Assert\Regex(
        pattern: '/^1[3-9]\d{9}$/',
        message: '请输入有效的手机号码'
    )]
    private ?string $registrantPhone = null;

    // Optional Fields
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarks = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialNotes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $prerequisiteNotes = null;

    // Lifecycle Stage Relationships
    #[ORM\OneToOne(targetEntity: PreliminaryDecision::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private ?PreliminaryDecision $preliminaryDecision = null;

    // System Fields
    #[ORM\Column(type: Types::STRING, enumType: ProjectStatus::class)]
    private ProjectStatus $status = ProjectStatus::DRAFT;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isCoreLocked = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCoreFields(): array
    {
        return [
            'projectName',
            'budget',
            'plannedStartDate',
            'plannedEndDate',
            'projectNature',
            'fundingSource',
        ];
    }

    public function __toString(): string
    {
        return $this->projectName ?? 'New Project';
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): self
    {
        $this->projectName = $projectName;
        return $this;
    }

    public function getProjectNumber(): ?string
    {
        return $this->projectNumber;
    }

    public function setProjectNumber(?string $projectNumber): self
    {
        $this->projectNumber = $projectNumber;
        return $this;
    }

    public function getProjectType(): ?ProjectType
    {
        return $this->projectType;
    }

    public function setProjectType(?ProjectType $projectType): self
    {
        $this->projectType = $projectType;
        return $this;
    }

    public function getProjectSubtype(): ?ProjectSubtype
    {
        return $this->projectSubtype;
    }

    public function setProjectSubtype(?ProjectSubtype $projectSubtype): self
    {
        $this->projectSubtype = $projectSubtype;
        return $this;
    }

    public function getProjectIndustry(): ?string
    {
        return $this->projectIndustry;
    }

    public function setProjectIndustry(string $projectIndustry): self
    {
        $this->projectIndustry = $projectIndustry;
        return $this;
    }

    public function getProjectLocation(): ?string
    {
        return $this->projectLocation;
    }

    public function setProjectLocation(string $projectLocation): self
    {
        $this->projectLocation = $projectLocation;
        return $this;
    }

    public function getProjectNature(): ?ProjectNature
    {
        return $this->projectNature;
    }

    public function setProjectNature(ProjectNature $projectNature): self
    {
        $this->projectNature = $projectNature;
        return $this;
    }

    public function getLeaderName(): ?string
    {
        return $this->leaderName;
    }

    public function setLeaderName(string $leaderName): self
    {
        $this->leaderName = $leaderName;
        return $this;
    }

    public function getLeaderPhone(): ?string
    {
        return $this->leaderPhone;
    }

    public function setLeaderPhone(string $leaderPhone): self
    {
        $this->leaderPhone = $leaderPhone;
        return $this;
    }

    public function getLeaderEmail(): ?string
    {
        return $this->leaderEmail;
    }

    public function setLeaderEmail(?string $leaderEmail): self
    {
        $this->leaderEmail = $leaderEmail;
        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(string $budget): self
    {
        $this->budget = $budget;
        return $this;
    }

    public function getPlannedStartDate(): ?\DateTimeImmutable
    {
        return $this->plannedStartDate;
    }

    public function setPlannedStartDate(\DateTimeImmutable $plannedStartDate): self
    {
        $this->plannedStartDate = $plannedStartDate;
        return $this;
    }

    public function getPlannedEndDate(): ?\DateTimeImmutable
    {
        return $this->plannedEndDate;
    }

    public function setPlannedEndDate(\DateTimeImmutable $plannedEndDate): self
    {
        $this->plannedEndDate = $plannedEndDate;
        return $this;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): self
    {
        $this->purpose = $purpose;
        return $this;
    }

    public function getScale(): ?string
    {
        return $this->scale;
    }

    public function setScale(string $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    public function getFundingSource(): ?FundingSource
    {
        return $this->fundingSource;
    }

    public function setFundingSource(FundingSource $fundingSource): self
    {
        $this->fundingSource = $fundingSource;
        return $this;
    }

    public function getRegistrantName(): ?string
    {
        return $this->registrantName;
    }

    public function setRegistrantName(string $registrantName): self
    {
        $this->registrantName = $registrantName;
        return $this;
    }

    public function getRegistrantOrganization(): ?string
    {
        return $this->registrantOrganization;
    }

    public function setRegistrantOrganization(string $registrantOrganization): self
    {
        $this->registrantOrganization = $registrantOrganization;
        return $this;
    }

    public function getRegistrantPhone(): ?string
    {
        return $this->registrantPhone;
    }

    public function setRegistrantPhone(string $registrantPhone): self
    {
        $this->registrantPhone = $registrantPhone;
        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;
        return $this;
    }

    public function getSpecialNotes(): ?string
    {
        return $this->specialNotes;
    }

    public function setSpecialNotes(?string $specialNotes): self
    {
        $this->specialNotes = $specialNotes;
        return $this;
    }

    public function getPrerequisiteNotes(): ?string
    {
        return $this->prerequisiteNotes;
    }

    public function setPrerequisiteNotes(?string $prerequisiteNotes): self
    {
        $this->prerequisiteNotes = $prerequisiteNotes;
        return $this;
    }

    public function getStatus(): ProjectStatus
    {
        return $this->status;
    }

    public function setStatus(ProjectStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isCoreLocked(): bool
    {
        return $this->isCoreLocked;
    }

    public function setIsCoreLocked(bool $isCoreLocked): self
    {
        $this->isCoreLocked = $isCoreLocked;
        return $this;
    }

    public function getPreliminaryDecision(): ?PreliminaryDecision
    {
        return $this->preliminaryDecision;
    }

    public function setPreliminaryDecision(?PreliminaryDecision $preliminaryDecision): self
    {
        // Set the owning side of the relation if necessary
        if ($preliminaryDecision !== null && $preliminaryDecision->getProject() !== $this) {
            $preliminaryDecision->setProject($this);
        }

        $this->preliminaryDecision = $preliminaryDecision;
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
