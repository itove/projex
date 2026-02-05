<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectStatus: string
{
    case DRAFT = 'draft';                              // 草稿
    case REGISTERED = 'registered';                    // 基础信息已登记
    case IN_PRELIMINARY_DECISION = 'in_preliminary';   // 前期决策中
    case PRELIMINARY_APPROVED = 'preliminary_approved'; // 前期决策已通过
    case IN_PROGRESS = 'in_progress';                  // 进行中
    case COMPLETED = 'completed';                      // 已完成
    case CANCELLED = 'cancelled';                      // 已取消

    public function label(): string
    {
        return match($this) {
            self::DRAFT => '草稿',
            self::REGISTERED => '基础信息已登记',
            self::IN_PRELIMINARY_DECISION => '前期决策中',
            self::PRELIMINARY_APPROVED => '前期决策已通过',
            self::IN_PROGRESS => '进行中',
            self::COMPLETED => '已完成',
            self::CANCELLED => '已取消',
        };
    }

    public function isCoreFieldsLocked(): bool
    {
        return match($this) {
            self::DRAFT => false,
            self::REGISTERED,
            self::IN_PRELIMINARY_DECISION,
            self::PRELIMINARY_APPROVED,
            self::IN_PROGRESS,
            self::COMPLETED,
            self::CANCELLED => true,
        };
    }
}
