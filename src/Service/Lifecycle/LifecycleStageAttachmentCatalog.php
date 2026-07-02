<?php

declare(strict_types=1);

namespace App\Service\Lifecycle;

/**
 * Attachment requirement lists per stage, sourced from docs/src/design.md §4.2.2.
 * Kept separate from the registry class to keep stage ordering readable.
 */
final class LifecycleStageAttachmentCatalog
{
  /** @var list<string> */
  private const STAGE_KEYS = [
    'preliminary',
    'approval',
    'planning',
    'preparation',
    'implementation',
    'acceptance',
    'settlement',
  ];

  /** @var array<string, string> */
  private const STAGE_LABELS = [
    'preliminary' => '前期决策',
    'approval' => '立项',
    'planning' => '规划设计',
    'preparation' => '施工准备',
    'implementation' => '施工实施',
    'acceptance' => '竣工验收',
    'settlement' => '结算',
  ];

    /**
     * @return list<LifecycleStageAttachmentRequirement>
     */
    public static function forStage(string $stageKey): array
    {
        return match ($stageKey) {
            'preliminary' => self::preliminary(),
            'approval' => self::approval(),
            'planning' => self::planning(),
            'preparation' => self::preparation(),
            'implementation' => self::implementation(),
            'acceptance' => self::acceptance(),
            'settlement' => self::settlement(),
            default => [],
        };
    }

    /**
     * @return array<string, string> label => key for form choices
     */
    public static function choiceMapForStage(string $stageKey): array
    {
        $choices = [];
        foreach (self::forStage($stageKey) as $requirement) {
            $choices[$requirement->label] = $requirement->key;
        }

        return $choices;
    }

    public static function findRequirement(string $stageKey, string $attachmentKey): ?LifecycleStageAttachmentRequirement
    {
        foreach (self::forStage($stageKey) as $requirement) {
            if ($requirement->key === $attachmentKey) {
                return $requirement;
            }
        }

        return null;
    }

    public static function labelForKey(string $attachmentKey): ?string
    {
        foreach (self::STAGE_KEYS as $stageKey) {
            $requirement = self::findRequirement($stageKey, $attachmentKey);
            if ($requirement !== null) {
                return $requirement->label;
            }
        }

        return null;
    }

    /**
     * @return array<string, string> unique label => key for global file admin
     */
    public static function allChoiceMap(): array
    {
        $choices = [];
        foreach (self::STAGE_KEYS as $stageKey) {
            $stageLabel = self::STAGE_LABELS[$stageKey];
            foreach (self::forStage($stageKey) as $requirement) {
                $choices[sprintf('%s · %s', $stageLabel, $requirement->label)] = $requirement->key;
            }
        }

        return $choices;
    }

    /**
     * @return list<LifecycleStageAttachmentRequirement>
     */
    private static function preliminary(): array
    {
        return [
            new LifecycleStageAttachmentRequirement('project_proposal', '项目建议书'),
            new LifecycleStageAttachmentRequirement('feasibility_study', '可行性研究报告', maxSizeMb: 100),
            new LifecycleStageAttachmentRequirement('feasibility_org_qualification', '可行性研究编制单位资质证明', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 20),
            new LifecycleStageAttachmentRequirement('scheme_comparison', '方案比选详细文件', required: false),
            new LifecycleStageAttachmentRequirement('financial_evaluation', '财务评价报告', required: false, allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('funding_arrangement', '资金筹措方案及审批文件'),
            new LifecycleStageAttachmentRequirement('preliminary_approval', '前期决策审批文件', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('preliminary_supplement', '其他补充文档', required: false),
        ];
    }

    /**
     * @return list<LifecycleStageAttachmentRequirement>
     */
    private static function approval(): array
    {
        return [
            new LifecycleStageAttachmentRequirement('approval_application', '立项申请表', maxSizeMb: 20),
            new LifecycleStageAttachmentRequirement('approval_decision', '立项批复文件', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('prior_approval_documents', '前置审批文件', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('approval_meeting_minutes', '立项审批会议纪要', required: false, maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('approval_supplement', '其他补充文档', required: false),
        ];
    }

    /**
     * @return list<LifecycleStageAttachmentRequirement>
     */
    private static function planning(): array
    {
        return [
            new LifecycleStageAttachmentRequirement('planning_permit_documents', '规划审批相关文件', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('planning_scheme', '规划方案及审批文件', allowedExtensions: ['pdf', 'doc', 'docx', 'dwg'], maxSizeMb: 100),
            new LifecycleStageAttachmentRequirement('preliminary_design', '初步设计文件及概算', allowedExtensions: ['pdf', 'doc', 'docx', 'dwg'], maxSizeMb: 100),
            new LifecycleStageAttachmentRequirement('preliminary_design_approval', '初步设计审批文件', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('construction_drawings', '施工图设计文件', allowedExtensions: ['pdf', 'doc', 'docx', 'dwg'], maxSizeMb: 200),
            new LifecycleStageAttachmentRequirement('drawing_review_certificate', '施工图审查合格书', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('construction_planning_permit', '建设工程规划许可证', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('design_org_qualification', '设计单位、施工图审查机构资质证明', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 20),
            new LifecycleStageAttachmentRequirement('planning_supplement', '其他补充文档', required: false),
        ];
    }

    /**
     * @return list<LifecycleStageAttachmentRequirement>
     */
    private static function preparation(): array
    {
        return [
            new LifecycleStageAttachmentRequirement('tender_documents', '招标文件'),
            new LifecycleStageAttachmentRequirement('tender_notice', '招标公告', allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'png'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('award_notice', '中标通知书', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('bid_evaluation_report', '评标报告'),
            new LifecycleStageAttachmentRequirement('construction_contracts', '施工合同、监理合同', allowedExtensions: ['jpg', 'png', 'pdf']),
            new LifecycleStageAttachmentRequirement('contractor_qualification', '施工单位、监理单位资质证明', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 20),
            new LifecycleStageAttachmentRequirement('construction_organization_plan', '施工组织设计及审批文件'),
            new LifecycleStageAttachmentRequirement('supervision_plan', '监理规划及监理实施细则', required: false),
            new LifecycleStageAttachmentRequirement('construction_permit', '建设工程施工许可证', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('procurement_plan', '材料设备采购计划及供应商资质', required: false),
            new LifecycleStageAttachmentRequirement('preparation_supplement', '其他补充文档', required: false),
        ];
    }

    /**
     * @return list<LifecycleStageAttachmentRequirement>
     */
    private static function implementation(): array
    {
        return [
            new LifecycleStageAttachmentRequirement('progress_report', '实施进度报告'),
            new LifecycleStageAttachmentRequirement('site_photos', '现场施工/集成照片', allowedExtensions: ['jpg', 'png'], maxSizeMb: 10),
            new LifecycleStageAttachmentRequirement('subproject_acceptance', '分部分项工程验收记录', maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('hidden_work_acceptance', '隐蔽工程验收记录', allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'png'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('change_request', '工程变更申请及审批文件', allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'png'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('material_inspection', '材料进场验收记录及产品合格证、检验报告', allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'png'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('supervision_report', '监理报告', required: false),
            new LifecycleStageAttachmentRequirement('safety_records', '安全记录', required: false, maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('inspection_rectification', '中间检查整改通知及回复', allowedExtensions: ['pdf', 'doc', 'docx'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('implementation_supplement', '其他补充文档', required: false),
        ];
    }

    /**
     * @return list<LifecycleStageAttachmentRequirement>
     */
    private static function acceptance(): array
    {
        return [
            new LifecycleStageAttachmentRequirement('acceptance_application', '验收申请表', maxSizeMb: 20),
            new LifecycleStageAttachmentRequirement('pre_acceptance_report', '竣工预验收报告及整改回复'),
            new LifecycleStageAttachmentRequirement('completion_acceptance_report', '竣工验收报告'),
            new LifecycleStageAttachmentRequirement('acceptance_meeting_minutes', '验收会议纪要', maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('acceptance_signatures', '验收签字文件', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('special_acceptance_certificates', '专项验收合格证明', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('rectification_report', '整改报告', allowedExtensions: ['pdf', 'doc', 'docx'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('completion_documents', '项目竣工资料', allowedExtensions: ['pdf', 'doc', 'docx', 'dwg'], maxSizeMb: 100),
            new LifecycleStageAttachmentRequirement('acceptance_filing_form', '竣工验收备案表', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('archive_handover_proof', '工程档案移交证明', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('acceptance_supplement', '其他补充文档', required: false),
        ];
    }

    /**
     * @return list<LifecycleStageAttachmentRequirement>
     */
    private static function settlement(): array
    {
        return [
            new LifecycleStageAttachmentRequirement('settlement_statement', '竣工结算书', allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx']),
            new LifecycleStageAttachmentRequirement('settlement_audit_report', '竣工结算审核报告'),
            new LifecycleStageAttachmentRequirement('final_accounts_report', '竣工决算报告', allowedExtensions: ['pdf', 'doc', 'docx', 'xls', 'xlsx']),
            new LifecycleStageAttachmentRequirement('financial_details', '财务收支明细', allowedExtensions: ['xls', 'xlsx', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('invoice_summary', '发票汇总表及关键发票扫描件', allowedExtensions: ['jpg', 'png', 'pdf', 'xls', 'xlsx']),
            new LifecycleStageAttachmentRequirement('settlement_agreement', '竣工结算协议', allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('quality_warranty', '工程质量保修书', maxSizeMb: 20),
            new LifecycleStageAttachmentRequirement('audit_report', '审计报告', required: false),
            new LifecycleStageAttachmentRequirement('payment_voucher', '资金拨付凭证', required: false, allowedExtensions: ['jpg', 'png', 'pdf'], maxSizeMb: 30),
            new LifecycleStageAttachmentRequirement('settlement_supplement', '其他补充文档', required: false),
        ];
    }
}
