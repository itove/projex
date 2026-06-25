<?php

declare(strict_types=1);

namespace App\DTO;

final class OrgOverviewProjectItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $projectNumber,
        public readonly string $statusLabel,
        public readonly string $detailUrl,
        public readonly string $leader,
    ) {
    }
}
