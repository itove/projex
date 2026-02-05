<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectType: string
{
    case CONSTRUCTION = 'construction';  // 施工类
    case INTEGRATION = 'integration';    // 集成类

    public function label(): string
    {
        return match($this) {
            self::CONSTRUCTION => '施工类',
            self::INTEGRATION => '集成类',
        };
    }

    public function getSubtypes(): array
    {
        return match($this) {
            self::CONSTRUCTION => [
                'municipal' => '市政工程',
                'building' => '建筑工程',
                'water' => '水利工程',
                'transportation' => '交通工程',
                'other' => '其他施工类',
            ],
            self::INTEGRATION => [
                'smart_city' => '智慧城市',
                'informatization' => '信息化建设',
                'security' => '安防系统',
                'environmental' => '环境治理',
                'other' => '其他集成类',
            ],
        };
    }
}
