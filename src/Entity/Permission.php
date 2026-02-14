<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permission')]
#[ORM\Index(columns: ['permission_code'], name: 'idx_permission_code')]
#[ORM\Index(columns: ['module'], name: 'idx_module')]
#[ORM\HasLifecycleCallbacks]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    #[Assert\NotBlank(message: '权限编码不能为空')]
    private ?string $permissionCode = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: '权限名称不能为空')]
    private ?string $permissionName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $permissionDescription = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank(message: '所属模块不能为空')]
    private ?string $module = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank(message: '操作类型不能为空')]
    private ?string $operationType = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $permissionLevel = 1;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_permission_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?self $parentPermission = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentPermission')]
    private Collection $children;

    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'permissions')]
    private Collection $roles;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->roles = new ArrayCollection();
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
        return $this->permissionName ?? 'New Permission';
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPermissionCode(): ?string
    {
        return $this->permissionCode;
    }

    public function setPermissionCode(string $permissionCode): self
    {
        $this->permissionCode = $permissionCode;
        return $this;
    }

    public function getPermissionName(): ?string
    {
        return $this->permissionName;
    }

    public function setPermissionName(string $permissionName): self
    {
        $this->permissionName = $permissionName;
        return $this;
    }

    public function getPermissionDescription(): ?string
    {
        return $this->permissionDescription;
    }

    public function setPermissionDescription(?string $permissionDescription): self
    {
        $this->permissionDescription = $permissionDescription;
        return $this;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;
        return $this;
    }

    public function getOperationType(): ?string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): self
    {
        $this->operationType = $operationType;
        return $this;
    }

    public function getPermissionLevel(): int
    {
        return $this->permissionLevel;
    }

    public function setPermissionLevel(int $permissionLevel): self
    {
        $this->permissionLevel = $permissionLevel;
        return $this;
    }

    public function getParentPermission(): ?self
    {
        return $this->parentPermission;
    }

    public function setParentPermission(?self $parentPermission): self
    {
        $this->parentPermission = $parentPermission;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParentPermission($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParentPermission() === $this) {
                $child->setParentPermission(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
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
