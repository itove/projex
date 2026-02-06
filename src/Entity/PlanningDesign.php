<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlanningDesignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlanningDesignRepository::class)]
#[ORM\Table(name: 'planning_design')]
#[ORM\HasLifecycleCallbacks]
class PlanningDesign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Project::class, inversedBy: 'planningDesign')]
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
    #[Assert\Length(max: 255, maxMessage: '设计单位不能超过 {{ limit }} 个字符')]
    private ?string $designUnit = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: '设计文件编号不能超过 {{ limit }} 个字符')]
    private ?string $designDocumentNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $preliminaryDesignDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $technicalDesignDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $constructionDrawingDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $budgetEstimateDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $designReviewDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $designApprovalDetails = null;

    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'planningDesign', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'planningDesign', cascade: ['persist', 'remove'], orphanRemoval: true)]
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
        return '规划设计流程 - ' . ($this->project?->getProjectName() ?? 'N/A');
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

    public function getDesignUnit(): ?string
    {
        return $this->designUnit;
    }

    public function setDesignUnit(?string $designUnit): self
    {
        $this->designUnit = $designUnit;
        return $this;
    }

    public function getDesignDocumentNumber(): ?string
    {
        return $this->designDocumentNumber;
    }

    public function setDesignDocumentNumber(?string $designDocumentNumber): self
    {
        $this->designDocumentNumber = $designDocumentNumber;
        return $this;
    }

    public function getPreliminaryDesignDetails(): ?string
    {
        return $this->preliminaryDesignDetails;
    }

    public function setPreliminaryDesignDetails(?string $preliminaryDesignDetails): self
    {
        $this->preliminaryDesignDetails = $preliminaryDesignDetails;
        return $this;
    }

    public function getTechnicalDesignDetails(): ?string
    {
        return $this->technicalDesignDetails;
    }

    public function setTechnicalDesignDetails(?string $technicalDesignDetails): self
    {
        $this->technicalDesignDetails = $technicalDesignDetails;
        return $this;
    }

    public function getConstructionDrawingDetails(): ?string
    {
        return $this->constructionDrawingDetails;
    }

    public function setConstructionDrawingDetails(?string $constructionDrawingDetails): self
    {
        $this->constructionDrawingDetails = $constructionDrawingDetails;
        return $this;
    }

    public function getBudgetEstimateDetails(): ?string
    {
        return $this->budgetEstimateDetails;
    }

    public function setBudgetEstimateDetails(?string $budgetEstimateDetails): self
    {
        $this->budgetEstimateDetails = $budgetEstimateDetails;
        return $this;
    }

    public function getDesignReviewDetails(): ?string
    {
        return $this->designReviewDetails;
    }

    public function setDesignReviewDetails(?string $designReviewDetails): self
    {
        $this->designReviewDetails = $designReviewDetails;
        return $this;
    }

    public function getDesignApprovalDetails(): ?string
    {
        return $this->designApprovalDetails;
    }

    public function setDesignApprovalDetails(?string $designApprovalDetails): self
    {
        $this->designApprovalDetails = $designApprovalDetails;
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
            $file->setPlanningDesign($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            if ($file->getPlanningDesign() === $this) {
                $file->setPlanningDesign(null);
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
            $image->setPlanningDesign($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getPlanningDesign() === $this) {
                $image->setPlanningDesign(null);
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
