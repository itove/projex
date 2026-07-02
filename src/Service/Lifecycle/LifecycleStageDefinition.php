<?php

declare(strict_types=1);

namespace App\Service\Lifecycle;

use App\Entity\LifecycleStageInterface;

/**
 * Immutable description of a single project lifecycle stage: its display
 * metadata (name/icon/route), which entity class backs it, and how to pull
 * a one-line summary out of that entity. This is the only place that needs
 * to change to rename, re-icon, reorder, or add a stage - adding a new
 * stage never requires touching Project itself.
 */
final readonly class LifecycleStageDefinition
{
    /**
     * @param class-string<LifecycleStageInterface> $entityClass
     * @param \Closure(?LifecycleStageInterface): ?string $infoAccessor
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $progressLabel,
        public string $icon,
        public string $route,
        public string $entityClass,
        public string $requirementsHint,
        private \Closure $infoAccessor,
    ) {
    }

    public function getInfo(?LifecycleStageInterface $entity): ?string
    {
        return ($this->infoAccessor)($entity);
    }
}
