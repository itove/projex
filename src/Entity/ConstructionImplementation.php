<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConstructionImplementationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConstructionImplementationRepository::class)]
#[ORM\Table(name: 'construction_implementation')]
#[ORM\HasLifecycleCallbacks]
class ConstructionImplementation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Project::class, inversedBy: 'constructionImplementation')]
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $constructionProgressDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $qualityInspectionDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $safetyInspectionDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $progressPaymentDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $changeOrderDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $supervisionDetails = null;

    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'constructionImplementation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'constructionImplementation', cascade: ['persist', 'remove'], orphanRemoval: true)]
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
        return '施工实施流程 - ' . ($this->project?->getProjectName() ?? 'N/A');
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

    public function getConstructionProgressDetails(): ?string
    {
        return $this->constructionProgressDetails;
    }

    public function setConstructionProgressDetails(?string $constructionProgressDetails): self
    {
        $this->constructionProgressDetails = $constructionProgressDetails;
        return $this;
    }

    public function getQualityInspectionDetails(): ?string
    {
        return $this->qualityInspectionDetails;
    }

    public function setQualityInspectionDetails(?string $qualityInspectionDetails): self
    {
        $this->qualityInspectionDetails = $qualityInspectionDetails;
        return $this;
    }

    public function getSafetyInspectionDetails(): ?string
    {
        return $this->safetyInspectionDetails;
    }

    public function setSafetyInspectionDetails(?string $safetyInspectionDetails): self
    {
        $this->safetyInspectionDetails = $safetyInspectionDetails;
        return $this;
    }

    public function getProgressPaymentDetails(): ?string
    {
        return $this->progressPaymentDetails;
    }

    public function setProgressPaymentDetails(?string $progressPaymentDetails): self
    {
        $this->progressPaymentDetails = $progressPaymentDetails;
        return $this;
    }

    public function getChangeOrderDetails(): ?string
    {
        return $this->changeOrderDetails;
    }

    public function setChangeOrderDetails(?string $changeOrderDetails): self
    {
        $this->changeOrderDetails = $changeOrderDetails;
        return $this;
    }

    public function getSupervisionDetails(): ?string
    {
        return $this->supervisionDetails;
    }

    public function setSupervisionDetails(?string $supervisionDetails): self
    {
        $this->supervisionDetails = $supervisionDetails;
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
            $file->setConstructionImplementation($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            if ($file->getConstructionImplementation() === $this) {
                $file->setConstructionImplementation(null);
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
            $image->setConstructionImplementation($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getConstructionImplementation() === $this) {
                $image->setConstructionImplementation(null);
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
