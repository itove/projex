<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Project;

class ProjectLockingService
{
    public function shouldLockCoreFields(Project $project): bool
    {
        return $project->getStatus()->isCoreFieldsLocked();
    }

    public function getCoreFieldNames(): array
    {
        return [
            'projectName',
            'budget',
            'plannedStartDate',
            'plannedEndDate',
            'projectNature',
            'fundingSource',
        ];
    }

    public function lockCoreFields(Project $project): void
    {
        if ($this->shouldLockCoreFields($project)) {
            $project->setIsCoreLocked(true);
        }
    }
}
