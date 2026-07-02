<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Canonical identifier for a project lifecycle stage type.
 *
 * Values match ProjectLifecycleStageRegistry keys exactly. Tasks reference
 * a stage type here rather than a FK to a stage entity row, because a task
 * may belong to a stage before that stage's entity exists for the project.
 */
enum ProjectLifecycleStage: string
{
    case Preliminary = 'preliminary';
    case Approval = 'approval';
    case Planning = 'planning';
    case Preparation = 'preparation';
    case Implementation = 'implementation';
    case Acceptance = 'acceptance';
    case Settlement = 'settlement';

    public function label(): string
    {
        return match ($this) {
            self::Preliminary => '前期决策流程',
            self::Approval => '立项流程',
            self::Planning => '规划设计流程',
            self::Preparation => '施工准备流程',
            self::Implementation => '施工实施流程',
            self::Acceptance => '竣工验收流程',
            self::Settlement => '竣工结算流程',
        };
    }

    public static function tryFromKey(?string $key): ?self
    {
        if ($key === null || $key === '') {
            return null;
        }

        return self::tryFrom($key);
    }
}
