<?php

declare(strict_types=1);

namespace App\DTO;

final class OrgOverviewNode
{
    /**
     * @param list<OrgOverviewNode> $children
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $orgCode,
        public readonly ?string $contactPerson,
        public readonly int $directProjectCount,
        public int $totalProjectCount,
        public readonly string $projectListUrl,
        public array $children = [],
    ) {
    }
}
