<?php

declare(strict_types=1);

namespace App\Service\Lifecycle;

use App\Entity\LifecycleStageInterface;
use App\Entity\Project;

/**
 * Immutable description of a single project lifecycle stage: its display
 * metadata (name/icon/route) plus how to pull its entity and summary info
 * off a Project. This is the only place that needs to change to rename,
 * re-icon, or re-order a stage.
 */
final readonly class LifecycleStageDefinition
{
    /**
     * @param \Closure(Project): ?LifecycleStageInterface $entityAccessor
     * @param \Closure(?LifecycleStageInterface): ?string $infoAccessor
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $progressLabel,
        public string $icon,
        public string $route,
        public string $projectProperty,
        public string $requirementsHint,
        private \Closure $entityAccessor,
        private \Closure $infoAccessor,
    ) {
    }

    public function getEntity(Project $project): ?LifecycleStageInterface
    {
        return ($this->entityAccessor)($project);
    }

    public function getInfo(?LifecycleStageInterface $entity): ?string
    {
        return ($this->infoAccessor)($entity);
    }
}
