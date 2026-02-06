<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompletionAcceptanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompletionAcceptanceRepository::class)]
#[ORM\Table(name: 'completion_acceptance')]
#[ORM\Index(columns: ['acceptance_date'], name: 'idx_completion_acceptance_date')]
#[ORM\Index(columns: ['created_at'], name: 'idx_completion_acceptance_created_at')]
#[ORM\HasLifecycleCallbacks]
class CompletionAcceptance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Project::class, inversedBy: 'completionAcceptance')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: '所属项目不能为空')]
    private ?Project $project = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $acceptanceDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $completionReportDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $qualityEvaluationDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $acceptanceInspectionDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $defectRectificationDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $finalAccountsDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $archiveDocumentationDetails = null;

    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'completionAcceptance')]
    private Collection $files;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'completionAcceptance')]
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
        return $this->project?->getProjectName() . ' - 竣工验收' ?? 'CompletionAcceptance #' . $this->id;
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

    public function getAcceptanceDate(): ?\DateTimeImmutable
    {
        return $this->acceptanceDate;
    }

    public function setAcceptanceDate(?\DateTimeImmutable $acceptanceDate): self
    {
        $this->acceptanceDate = $acceptanceDate;
        return $this;
    }

    public function getCompletionReportDetails(): ?string
    {
        return $this->completionReportDetails;
    }

    public function setCompletionReportDetails(?string $completionReportDetails): self
    {
        $this->completionReportDetails = $completionReportDetails;
        return $this;
    }

    public function getQualityEvaluationDetails(): ?string
    {
        return $this->qualityEvaluationDetails;
    }

    public function setQualityEvaluationDetails(?string $qualityEvaluationDetails): self
    {
        $this->qualityEvaluationDetails = $qualityEvaluationDetails;
        return $this;
    }

    public function getAcceptanceInspectionDetails(): ?string
    {
        return $this->acceptanceInspectionDetails;
    }

    public function setAcceptanceInspectionDetails(?string $acceptanceInspectionDetails): self
    {
        $this->acceptanceInspectionDetails = $acceptanceInspectionDetails;
        return $this;
    }

    public function getDefectRectificationDetails(): ?string
    {
        return $this->defectRectificationDetails;
    }

    public function setDefectRectificationDetails(?string $defectRectificationDetails): self
    {
        $this->defectRectificationDetails = $defectRectificationDetails;
        return $this;
    }

    public function getFinalAccountsDetails(): ?string
    {
        return $this->finalAccountsDetails;
    }

    public function setFinalAccountsDetails(?string $finalAccountsDetails): self
    {
        $this->finalAccountsDetails = $finalAccountsDetails;
        return $this;
    }

    public function getArchiveDocumentationDetails(): ?string
    {
        return $this->archiveDocumentationDetails;
    }

    public function setArchiveDocumentationDetails(?string $archiveDocumentationDetails): self
    {
        $this->archiveDocumentationDetails = $archiveDocumentationDetails;
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
            $file->setCompletionAcceptance($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            if ($file->getCompletionAcceptance() === $this) {
                $file->setCompletionAcceptance(null);
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
            $image->setCompletionAcceptance($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getCompletionAcceptance() === $this) {
                $image->setCompletionAcceptance(null);
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
