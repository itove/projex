<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PreliminaryDecisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PreliminaryDecisionRepository::class)]
#[ORM\Table(name: 'preliminary_decision')]
#[ORM\HasLifecycleCallbacks]
class PreliminaryDecision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Project::class, inversedBy: 'preliminaryDecision')]
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
    #[Assert\Length(max: 255, maxMessage: '组织单位不能超过 {{ limit }} 个字符')]
    private ?string $organizingUnit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $projectProposalDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $feasibilityStudyDetails = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: '可研编制单位不能超过 {{ limit }} 个字符')]
    private ?string $feasibilityStudyOrganization = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fundingArrangementDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $approvalOpinions = null;

    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'preliminaryDecision', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'preliminaryDecision', cascade: ['persist', 'remove'], orphanRemoval: true)]
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
        return '前期决策 - ' . ($this->project?->getProjectName() ?? 'N/A');
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

    public function getOrganizingUnit(): ?string
    {
        return $this->organizingUnit;
    }

    public function setOrganizingUnit(?string $organizingUnit): self
    {
        $this->organizingUnit = $organizingUnit;
        return $this;
    }

    public function getProjectProposalDetails(): ?string
    {
        return $this->projectProposalDetails;
    }

    public function setProjectProposalDetails(?string $projectProposalDetails): self
    {
        $this->projectProposalDetails = $projectProposalDetails;
        return $this;
    }

    public function getFeasibilityStudyDetails(): ?string
    {
        return $this->feasibilityStudyDetails;
    }

    public function setFeasibilityStudyDetails(?string $feasibilityStudyDetails): self
    {
        $this->feasibilityStudyDetails = $feasibilityStudyDetails;
        return $this;
    }

    public function getFeasibilityStudyOrganization(): ?string
    {
        return $this->feasibilityStudyOrganization;
    }

    public function setFeasibilityStudyOrganization(?string $feasibilityStudyOrganization): self
    {
        $this->feasibilityStudyOrganization = $feasibilityStudyOrganization;
        return $this;
    }

    public function getFundingArrangementDetails(): ?string
    {
        return $this->fundingArrangementDetails;
    }

    public function setFundingArrangementDetails(?string $fundingArrangementDetails): self
    {
        $this->fundingArrangementDetails = $fundingArrangementDetails;
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
            $file->setPreliminaryDecision($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            if ($file->getPreliminaryDecision() === $this) {
                $file->setPreliminaryDecision(null);
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
            $image->setPreliminaryDecision($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getPreliminaryDecision() === $this) {
                $image->setPreliminaryDecision(null);
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
