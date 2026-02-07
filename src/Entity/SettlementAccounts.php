<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SettlementAccountsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SettlementAccountsRepository::class)]
#[ORM\Table(name: 'settlement_accounts')]
#[ORM\Index(columns: ['settlement_date'], name: 'idx_settlement_date')]
#[ORM\Index(columns: ['created_at'], name: 'idx_settlement_accounts_created_at')]
#[ORM\HasLifecycleCallbacks]
class SettlementAccounts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Project::class, inversedBy: 'settlementAccounts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: '所属项目不能为空')]
    private ?Project $project = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $settlementDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contractSettlementDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $costAuditDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $quantitySurveyDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $disputeResolutionDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paymentCompletionDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $warrantyManagementDetails = null;

    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'settlementAccounts', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'settlementAccounts', cascade: ['persist', 'remove'], orphanRemoval: true)]
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
        return $this->project?->getProjectName() . ' - 结算' ?? 'SettlementAccounts #' . $this->id;
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

    public function getSettlementDate(): ?\DateTimeImmutable
    {
        return $this->settlementDate;
    }

    public function setSettlementDate(?\DateTimeImmutable $settlementDate): self
    {
        $this->settlementDate = $settlementDate;
        return $this;
    }

    public function getContractSettlementDetails(): ?string
    {
        return $this->contractSettlementDetails;
    }

    public function setContractSettlementDetails(?string $contractSettlementDetails): self
    {
        $this->contractSettlementDetails = $contractSettlementDetails;
        return $this;
    }

    public function getCostAuditDetails(): ?string
    {
        return $this->costAuditDetails;
    }

    public function setCostAuditDetails(?string $costAuditDetails): self
    {
        $this->costAuditDetails = $costAuditDetails;
        return $this;
    }

    public function getQuantitySurveyDetails(): ?string
    {
        return $this->quantitySurveyDetails;
    }

    public function setQuantitySurveyDetails(?string $quantitySurveyDetails): self
    {
        $this->quantitySurveyDetails = $quantitySurveyDetails;
        return $this;
    }

    public function getDisputeResolutionDetails(): ?string
    {
        return $this->disputeResolutionDetails;
    }

    public function setDisputeResolutionDetails(?string $disputeResolutionDetails): self
    {
        $this->disputeResolutionDetails = $disputeResolutionDetails;
        return $this;
    }

    public function getPaymentCompletionDetails(): ?string
    {
        return $this->paymentCompletionDetails;
    }

    public function setPaymentCompletionDetails(?string $paymentCompletionDetails): self
    {
        $this->paymentCompletionDetails = $paymentCompletionDetails;
        return $this;
    }

    public function getWarrantyManagementDetails(): ?string
    {
        return $this->warrantyManagementDetails;
    }

    public function setWarrantyManagementDetails(?string $warrantyManagementDetails): self
    {
        $this->warrantyManagementDetails = $warrantyManagementDetails;
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
            $file->setSettlementAccounts($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            if ($file->getSettlementAccounts() === $this) {
                $file->setSettlementAccounts(null);
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
            $image->setSettlementAccounts($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getSettlementAccounts() === $this) {
                $image->setSettlementAccounts(null);
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
