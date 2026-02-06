<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjectTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectTypeRepository::class)]
#[ORM\Table(name: 'project_type')]
#[ORM\Index(columns: ['code'], name: 'idx_project_type_code')]
#[ORM\Index(columns: ['is_active'], name: 'idx_project_type_active')]
#[UniqueEntity(fields: ['code'], message: '该项目类型代码已存在')]
#[ORM\HasLifecycleCallbacks]
class ProjectType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    #[Assert\NotBlank(message: '类型代码不能为空')]
    #[Assert\Length(max: 50, maxMessage: '类型代码不能超过 {{ limit }} 个字符')]
    #[Assert\Regex(pattern: '/^[a-z_]+$/', message: '类型代码只能包含小写字母和下划线')]
    private ?string $code = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: '类型名称不能为空')]
    #[Assert\Length(max: 100, maxMessage: '类型名称不能超过 {{ limit }} 个字符')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\PositiveOrZero(message: '排序值必须大于或等于 0')]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: ProjectSubtype::class, mappedBy: 'projectType')]
    private Collection $subtypes;

    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'projectType')]
    private Collection $projects;

    public function __construct()
    {
        $this->subtypes = new ArrayCollection();
        $this->projects = new ArrayCollection();
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
        return $this->name ?? 'New Project Type';
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
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

    /**
     * @return Collection<int, ProjectSubtype>
     */
    public function getSubtypes(): Collection
    {
        return $this->subtypes;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }
}
