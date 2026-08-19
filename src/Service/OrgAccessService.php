<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\LifecycleStageInterface;
use App\Entity\Org;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\OrgRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class OrgAccessService
{
    public function __construct(
        private readonly OrgRepository $orgRepository,
        private readonly Security $security,
    ) {
    }

    public function bypassesOrgScope(): bool
    {
        return $this->security->isGranted('ROLE_SYSTEM_ADMIN');
    }

    /**
     * @return list<int>|null null means no org restriction (system admin)
     */
    public function getAccessibleOrgIds(?User $user): ?array
    {
        if ($user === null || $this->bypassesOrgScope()) {
            return null;
        }

        $org = $user->getOrg();
        if ($org === null || $org->getId() === null) {
            return [];
        }

        return $this->orgRepository->findDescendantIds($org->getId());
    }

    public function canAccessOrg(?User $user, ?Org $org): bool
    {
        if ($org === null || $org->getId() === null) {
            return false;
        }

        $accessibleOrgIds = $this->getAccessibleOrgIds($user);
        if ($accessibleOrgIds === null) {
            return true;
        }

        return in_array($org->getId(), $accessibleOrgIds, true);
    }

    public function canViewProject(?User $user, ?Project $project): bool
    {
        if ($project === null) {
            return false;
        }

        if (!$this->canAccessOrg($user, $project->getOrg())) {
            return false;
        }

        return $this->security->isGranted('ROLE_USER');
    }

    public function canManageProject(?User $user, ?Project $project): bool
    {
        if (!$this->canViewProject($user, $project)) {
            return false;
        }

        return $this->security->isGranted('ROLE_PROJECT_MANAGER')
            || $this->security->isGranted('ROLE_SYSTEM_ADMIN');
    }

    public function applyProjectOrgScope(QueryBuilder $qb, ?User $user, string $projectAlias = 'entity'): void
    {
        $accessibleOrgIds = $this->getAccessibleOrgIds($user);
        if ($accessibleOrgIds === null) {
            return;
        }

        if ($accessibleOrgIds === []) {
            $qb->andWhere('1 = 0');

            return;
        }

        $qb->andWhere(sprintf('%s.org IN (:accessibleOrgIds)', $projectAlias))
            ->setParameter('accessibleOrgIds', $accessibleOrgIds);
    }

    public function applyProjectRelationOrgScope(QueryBuilder $qb, ?User $user, string $projectRelationAlias = 'project'): void
    {
        $accessibleOrgIds = $this->getAccessibleOrgIds($user);
        if ($accessibleOrgIds === null) {
            return;
        }

        if ($accessibleOrgIds === []) {
            $qb->andWhere('1 = 0');

            return;
        }

        $qb->andWhere(sprintf('%s.org IN (:accessibleOrgIds)', $projectRelationAlias))
            ->setParameter('accessibleOrgIds', $accessibleOrgIds);
    }

    /**
     * Scope File/Image queries to projects in the user's org tree.
     * Files attach only via lifecycle stages; images may also have a direct project.
     */
    public function applyLifecycleAttachmentOrgScope(
        QueryBuilder $qb,
        ?User $user,
        string $entityAlias = 'entity',
        bool $includeDirectProject = false,
    ): void {
        $accessibleOrgIds = $this->getAccessibleOrgIds($user);
        if ($accessibleOrgIds === null) {
            return;
        }

        if ($accessibleOrgIds === []) {
            $qb->andWhere('1 = 0');

            return;
        }

        $stageRelations = [
            'preliminaryDecision',
            'projectApproval',
            'planningDesign',
            'constructionPreparation',
            'constructionImplementation',
            'completionAcceptance',
            'settlementAccounts',
        ];

        $orgPredicates = [];
        foreach ($stageRelations as $index => $relation) {
            $stageAlias = sprintf('%s_stage_%d', $entityAlias, $index);
            $projectAlias = sprintf('%s_proj_%d', $entityAlias, $index);
            $qb->leftJoin(sprintf('%s.%s', $entityAlias, $relation), $stageAlias)
                ->leftJoin(sprintf('%s.project', $stageAlias), $projectAlias);
            $orgPredicates[] = sprintf('%s.org IN (:accessibleOrgIds)', $projectAlias);
        }

        if ($includeDirectProject) {
            $directAlias = sprintf('%s_direct_project', $entityAlias);
            $qb->leftJoin(sprintf('%s.project', $entityAlias), $directAlias);
            $orgPredicates[] = sprintf('%s.org IN (:accessibleOrgIds)', $directAlias);
        }

        $qb->andWhere('(' . implode(' OR ', $orgPredicates) . ')')
            ->setParameter('accessibleOrgIds', $accessibleOrgIds)
            ->distinct(true);
    }

    /**
     * Resolve the owning project for a File or Image via stage or direct project link.
     */
    public function resolveOwningProject(object $attachment): ?Project
    {
        if (method_exists($attachment, 'getProject')) {
            $project = $attachment->getProject();
            if ($project instanceof Project) {
                return $project;
            }
        }

        $stageGetters = [
            'getPreliminaryDecision',
            'getProjectApproval',
            'getPlanningDesign',
            'getConstructionPreparation',
            'getConstructionImplementation',
            'getCompletionAcceptance',
            'getSettlementAccounts',
        ];

        foreach ($stageGetters as $getter) {
            if (!method_exists($attachment, $getter)) {
                continue;
            }

            $stage = $attachment->{$getter}();
            if ($stage instanceof LifecycleStageInterface) {
                $project = $stage->getProject();
                if ($project instanceof Project) {
                    return $project;
                }
            }
        }

        return null;
    }

    public function canViewLifecycleAttachment(?User $user, object $attachment): bool
    {
        $project = $this->resolveOwningProject($attachment);
        if ($project === null) {
            return $this->bypassesOrgScope();
        }

        return $this->canViewProject($user, $project);
    }

    public function wouldCreateCycle(Org $org, ?Org $newParent): bool
    {
        if ($newParent === null || $org->getId() === null || $newParent->getId() === null) {
            return false;
        }

        if ($org->getId() === $newParent->getId()) {
            return true;
        }

        return $this->orgRepository->isDescendantOf($newParent->getId(), $org->getId());
    }
}
