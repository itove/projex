<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConstructionPreparationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConstructionPreparationRepository::class)]
#[ORM\Table(name: 'construction_preparation')]
#[ORM\HasLifecycleCallbacks]
class ConstructionPreparation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Project::class, inversedBy: 'constructionPreparation')]
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
    #[Assert\Length(max: 255, maxMessage: '施工单位不能超过 {{ limit }} 个字符')]
    private ?string $constructionUnit = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: '施工许可证号不能超过 {{ limit }} 个字符')]
    private ?string $constructionPermitNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bidDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contractDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $constructionPlanDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $qualityControlDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $safetyPlanDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $environmentalProtectionDetails = null;

    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'constructionPreparation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'constructionPreparation', cascade: ['persist', 'remove'], orphanRemoval: true)]
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
        return '施工准备流程 - ' . ($this->project?->getProjectName() ?? 'N/A');
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

    public function getConstructionUnit(): ?string
    {
        return $this->constructionUnit;
    }

    public function setConstructionUnit(?string $constructionUnit): self
    {
        $this->constructionUnit = $constructionUnit;
        return $this;
    }

    public function getConstructionPermitNumber(): ?string
    {
        return $this->constructionPermitNumber;
    }

    public function setConstructionPermitNumber(?string $constructionPermitNumber): self
    {
        $this->constructionPermitNumber = $constructionPermitNumber;
        return $this;
    }

    public function getBidDetails(): ?string
    {
        return $this->bidDetails;
    }

    public function setBidDetails(?string $bidDetails): self
    {
        $this->bidDetails = $bidDetails;
        return $this;
    }

    public function getContractDetails(): ?string
    {
        return $this->contractDetails;
    }

    public function setContractDetails(?string $contractDetails): self
    {
        $this->contractDetails = $contractDetails;
        return $this;
    }

    public function getConstructionPlanDetails(): ?string
    {
        return $this->constructionPlanDetails;
    }

    public function setConstructionPlanDetails(?string $constructionPlanDetails): self
    {
        $this->constructionPlanDetails = $constructionPlanDetails;
        return $this;
    }

    public function getQualityControlDetails(): ?string
    {
        return $this->qualityControlDetails;
    }

    public function setQualityControlDetails(?string $qualityControlDetails): self
    {
        $this->qualityControlDetails = $qualityControlDetails;
        return $this;
    }

    public function getSafetyPlanDetails(): ?string
    {
        return $this->safetyPlanDetails;
    }

    public function setSafetyPlanDetails(?string $safetyPlanDetails): self
    {
        $this->safetyPlanDetails = $safetyPlanDetails;
        return $this;
    }

    public function getEnvironmentalProtectionDetails(): ?string
    {
        return $this->environmentalProtectionDetails;
    }

    public function setEnvironmentalProtectionDetails(?string $environmentalProtectionDetails): self
    {
        $this->environmentalProtectionDetails = $environmentalProtectionDetails;
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
            $file->setConstructionPreparation($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            if ($file->getConstructionPreparation() === $this) {
                $file->setConstructionPreparation(null);
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
            $image->setConstructionPreparation($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getConstructionPreparation() === $this) {
                $image->setConstructionPreparation(null);
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
