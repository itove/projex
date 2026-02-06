<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjectApprovalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectApprovalRepository::class)]
#[ORM\Table(name: 'project_approval')]
#[ORM\HasLifecycleCallbacks]
class ProjectApproval
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Project::class, inversedBy: 'projectApproval')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: '项目不能为空')]
    private ?Project $project = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(
        propertyPath: 'startDate',
        message: '完成日期必须晚于开始日期'
    )]
    private ?\DateTimeImmutable $completionDate = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: '审批机关不能超过 {{ limit }} 个字符')]
    private ?string $approvingAuthority = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: '批复文号不能超过 {{ limit }} 个字符')]
    private ?string $approvalDocumentNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $investmentApprovalDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $landUseApprovalDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $environmentalAssessmentDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $socialStabilityAssessmentDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $approvalOpinions = null;

    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'projectApproval', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'projectApproval', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

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

    public function __toString(): string
    {
        return '立项流程 - ' . ($this->project?->getProjectName() ?? 'N/A');
    }

    // Getters and Setters
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

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getCompletionDate(): ?\DateTimeImmutable
    {
        return $this->completionDate;
    }

    public function setCompletionDate(?\DateTimeImmutable $completionDate): self
    {
        $this->completionDate = $completionDate;
        return $this;
    }

    public function getApprovingAuthority(): ?string
    {
        return $this->approvingAuthority;
    }

    public function setApprovingAuthority(?string $approvingAuthority): self
    {
        $this->approvingAuthority = $approvingAuthority;
        return $this;
    }

    public function getApprovalDocumentNumber(): ?string
    {
        return $this->approvalDocumentNumber;
    }

    public function setApprovalDocumentNumber(?string $approvalDocumentNumber): self
    {
        $this->approvalDocumentNumber = $approvalDocumentNumber;
        return $this;
    }

    public function getInvestmentApprovalDetails(): ?string
    {
        return $this->investmentApprovalDetails;
    }

    public function setInvestmentApprovalDetails(?string $investmentApprovalDetails): self
    {
        $this->investmentApprovalDetails = $investmentApprovalDetails;
        return $this;
    }

    public function getLandUseApprovalDetails(): ?string
    {
        return $this->landUseApprovalDetails;
    }

    public function setLandUseApprovalDetails(?string $landUseApprovalDetails): self
    {
        $this->landUseApprovalDetails = $landUseApprovalDetails;
        return $this;
    }

    public function getEnvironmentalAssessmentDetails(): ?string
    {
        return $this->environmentalAssessmentDetails;
    }

    public function setEnvironmentalAssessmentDetails(?string $environmentalAssessmentDetails): self
    {
        $this->environmentalAssessmentDetails = $environmentalAssessmentDetails;
        return $this;
    }

    public function getSocialStabilityAssessmentDetails(): ?string
    {
        return $this->socialStabilityAssessmentDetails;
    }

    public function setSocialStabilityAssessmentDetails(?string $socialStabilityAssessmentDetails): self
    {
        $this->socialStabilityAssessmentDetails = $socialStabilityAssessmentDetails;
        return $this;
    }

    public function getApprovalOpinions(): ?string
    {
        return $this->approvalOpinions;
    }

    public function setApprovalOpinions(?string $approvalOpinions): self
    {
        $this->approvalOpinions = $approvalOpinions;
        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setProjectApproval($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            if ($file->getProjectApproval() === $this) {
                $file->setProjectApproval(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProjectApproval($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProjectApproval() === $this) {
                $image->setProjectApproval(null);
            }
        }

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
