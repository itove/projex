<?php

declare(strict_types=1);

namespace App\Enum;

enum FundingSource: string
{
    case GOVERNMENT_FISCAL = 'government_fiscal';    // 政府财政拨款
    case BANK_LOAN = 'bank_loan';                    // 银行贷款
    case SOCIAL_CAPITAL = 'social_capital';          // 社会资本
    case ENTERPRISE_OWNED = 'enterprise_owned';      // 企业自有资金

    public function label(): string
    {
        return match($this) {
            self::GOVERNMENT_FISCAL => '政府财政拨款',
            self::BANK_LOAN => '银行贷款',
            self::SOCIAL_CAPITAL => '社会资本',
            self::ENTERPRISE_OWNED => '企业自有资金',
        };
    }
}
