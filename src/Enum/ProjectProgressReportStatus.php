<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectProgressReportStatus: string
{
    case NORMAL = 'normal';
    case AT_RISK = 'at_risk';
    case DELAYED = 'delayed';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => '正常',
            self::AT_RISK => '预警',
            self::DELAYED => '滞后',
        };
    }
}
