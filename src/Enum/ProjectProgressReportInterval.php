<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectProgressReportInterval: string
{
    case WEEK = 'week';
    case MONTH = 'month';

    public function label(): string
    {
        return match ($this) {
            self::WEEK => '每周',
            self::MONTH => '每月',
        };
    }
}
