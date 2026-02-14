<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'role')]
#[ORM\Index(columns: ['role_code'], name: 'idx_role_code')]
#[ORM\HasLifecycleCallbacks]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    #[Assert\NotBlank(message: '角色编码不能为空')]
    private ?string $roleCode = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: '角色名称不能为空')]
    private ?string $roleName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $roleDescription = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isPreset = false;

    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'roles')]
    #[ORM\JoinTable(name: 'role_permission')]
    private Collection $permissions;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'userRoles')]
    private Collection $users;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->users = new ArrayCollection();
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
        return $this->roleName ?? 'New Role';
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleCode(): ?string
    {
        return $this->roleCode;
    }

    public function setRoleCode(string $roleCode): self
    {
        $this->roleCode = $roleCode;
        return $this;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(string $roleName): self
    {
        $this->roleName = $roleName;
        return $this;
    }

    public function getRoleDescription(): ?string
    {
        return $this->roleDescription;
    }

    public function setRoleDescription(?string $roleDescription): self
    {
        $this->roleDescription = $roleDescription;
        return $this;
    }

    public function isPreset(): bool
    {
        return $this->isPreset;
    }

    public function setIsPreset(bool $isPreset): self
    {
        $this->isPreset = $isPreset;
        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        $this->permissions->removeElement($permission);
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
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
