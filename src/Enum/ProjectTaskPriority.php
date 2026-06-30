<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectTaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => '低',
            self::MEDIUM => '中',
            self::HIGH => '高',
        };
    }
}
