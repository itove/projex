<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectNature: string
{
    case GOVERNMENT = 'government';  // 政府投资
    case ENTERPRISE = 'enterprise';  // 企业投资

    public function label(): string
    {
        return match($this) {
            self::GOVERNMENT => '政府投资',
            self::ENTERPRISE => '企业投资',
        };
    }
}
