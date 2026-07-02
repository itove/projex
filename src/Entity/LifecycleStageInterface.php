<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * Contract shared by all project lifecycle stage entities
 * (PreliminaryDecision, ProjectApproval, PlanningDesign,
 * ConstructionPreparation, ConstructionImplementation,
 * CompletionAcceptance, SettlementAccounts).
 *
 * Lets consumers (ProjectDisplayService, ProjectNavigationService, ...)
 * work with any stage generically instead of relying on method_exists()
 * checks or knowing each stage's concrete class.
 */
interface LifecycleStageInterface
{
    public function getProject(): ?Project;

    /**
     * Whether this stage should be considered fully completed
     * (as opposed to merely started/in progress).
     */
    public function isComplete(): bool;

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection;

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection;
}
