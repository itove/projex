<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectTaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => '待开始',
            self::IN_PROGRESS => '进行中',
            self::DONE => '已完成',
            self::CANCELLED => '已取消',
        };
    }

    public function isOpen(): bool
    {
        return match ($this) {
            self::PENDING, self::IN_PROGRESS => true,
            self::DONE, self::CANCELLED => false,
        };
    }
}
