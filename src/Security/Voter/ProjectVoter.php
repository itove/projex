<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use App\Service\OrgAccessService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Project>
 */
class ProjectVoter extends Voter
{
    public const VIEW = 'PROJECT_VIEW';
    public const MANAGE = 'PROJECT_MANAGE';

    public function __construct(
        private readonly OrgAccessService $orgAccessService,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::MANAGE], true)
            && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->orgAccessService->canViewProject($user, $subject),
            self::MANAGE => $this->orgAccessService->canManageProject($user, $subject),
            default => false,
        };
    }
}
