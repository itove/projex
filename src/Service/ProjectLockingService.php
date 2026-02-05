<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ProjectBase;

class ProjectLockingService
{
    public function shouldLockCoreFields(ProjectBase $project): bool
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

    public function lockCoreFields(ProjectBase $project): void
    {
        if ($this->shouldLockCoreFields($project)) {
            $project->setIsCoreLocked(true);
        }
    }
}
