<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\Table(name: 'image')]
#[ORM\Index(columns: ['created_at'], name: 'idx_image_created_at')]
#[ORM\Index(columns: ['preliminary_decision_id'], name: 'idx_image_preliminary_decision')]
#[ORM\Index(columns: ['project_approval_id'], name: 'idx_image_project_approval')]
#[ORM\Index(columns: ['planning_design_id'], name: 'idx_image_planning_design')]
#[ORM\Index(columns: ['construction_preparation_id'], name: 'idx_image_construction_preparation')]
#[ORM\Index(columns: ['construction_implementation_id'], name: 'idx_image_construction_implementation')]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PreliminaryDecision::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?PreliminaryDecision $preliminaryDecision = null;

    #[ORM\ManyToOne(targetEntity: ProjectApproval::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?ProjectApproval $projectApproval = null;

    #[ORM\ManyToOne(targetEntity: PlanningDesign::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?PlanningDesign $planningDesign = null;

    #[ORM\ManyToOne(targetEntity: ConstructionPreparation::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?ConstructionPreparation $constructionPreparation = null;

    #[ORM\ManyToOne(targetEntity: ConstructionImplementation::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?ConstructionImplementation $constructionImplementation = null;

    #[Vich\UploadableField(mapping: 'project_images', fileNameProperty: 'fileName', size: 'fileSize', mimeType: 'mimeType', originalName: 'originalName', dimensions: 'dimensions')]
    #[Assert\Image(
        maxSize: '10M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        mimeTypesMessage: '请上传有效的图片文件 (JPEG, PNG, GIF, WebP)'
    )]
    private ?SymfonyFile $imageFile = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $originalName = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\Positive]
    private ?int $fileSize = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $dimensions = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $altText = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $caption = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $category = null;

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

    public function __toString(): string
    {
        return $this->caption ?? $this->originalName ?? $this->fileName ?? 'Image #' . $this->id;
    }

    public function getWidth(): ?int
    {
        return $this->dimensions[0] ?? null;
    }

    public function getHeight(): ?int
    {
        return $this->dimensions[1] ?? null;
    }

    public function getDimensionsFormatted(): string
    {
        if ($this->dimensions === null || count($this->dimensions) < 2) {
            return 'Unknown';
        }

        return $this->dimensions[0] . ' × ' . $this->dimensions[1] . ' px';
    }

    public function getFileSizeFormatted(): string
    {
        if ($this->fileSize === null) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->fileSize;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageFile(): ?SymfonyFile
    {
        return $this->imageFile;
    }

    public function setImageFile(?SymfonyFile $imageFile): self
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    public function setDimensions(?array $dimensions): self
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): self
    {
        $this->altText = $altText;
        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getPreliminaryDecision(): ?PreliminaryDecision
    {
        return $this->preliminaryDecision;
    }

    public function setPreliminaryDecision(?PreliminaryDecision $preliminaryDecision): self
    {
        $this->preliminaryDecision = $preliminaryDecision;
        return $this;
    }

    public function getProjectApproval(): ?ProjectApproval
    {
        return $this->projectApproval;
    }

    public function setProjectApproval(?ProjectApproval $projectApproval): self
    {
        $this->projectApproval = $projectApproval;
        return $this;
    }

    public function getPlanningDesign(): ?PlanningDesign
    {
        return $this->planningDesign;
    }

    public function setPlanningDesign(?PlanningDesign $planningDesign): self
    {
        $this->planningDesign = $planningDesign;
        return $this;
    }

    public function getConstructionPreparation(): ?ConstructionPreparation
    {
        return $this->constructionPreparation;
    }

    public function setConstructionPreparation(?ConstructionPreparation $constructionPreparation): self
    {
        $this->constructionPreparation = $constructionPreparation;
        return $this;
    }

    public function getConstructionImplementation(): ?ConstructionImplementation
    {
        return $this->constructionImplementation;
    }

    public function setConstructionImplementation(?ConstructionImplementation $constructionImplementation): self
    {
        $this->constructionImplementation = $constructionImplementation;
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
