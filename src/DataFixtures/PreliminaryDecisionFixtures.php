<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PreliminaryDecision;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PreliminaryDecisionFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $projectRepo = $manager->getRepository(Project::class);

        // Define preliminary decisions for specific projects
        $preliminaryDecisions = [
            [
                'projectName' => '东城区市政道路改造工程',
                'startDate' => '2025-10-01',
                'completionDate' => '2026-01-15',
                'organizingUnit' => '东城区住建委',
                'projectProposalDetails' => '项目建议书已完成编制，提出了改善老城区道路状况的必要性和可行性，得到了区政府的初步认可。',
                'feasibilityStudyDetails' => '由北京市市政工程设计研究总院编制完成，对道路改造的技术方案、工程造价、经济效益进行了详细分析。方案包括路面翻新、雨污分流、照明系统升级等内容。经专家评审，认为方案合理可行。',
                'feasibilityStudyOrganization' => '北京市市政工程设计研究总院',
                'fundingArrangementDetails' => '项目总投资1200万元，全部由区财政资金安排，已纳入2026年度区级重点工程投资计划。',
                'approvalOpinions' => '经区政府常务会议研究，同意实施该项目。要求严格按照批准的方案组织实施，确保工程质量和进度。',
            ],
            [
                'projectName' => '水利枢纽工程建设',
                'startDate' => '2025-08-01',
                'completionDate' => '2026-02-28',
                'organizingUnit' => '省水利厅',
                'projectProposalDetails' => '项目建议书由省水利规划设计院编制，提出建设区域性水利枢纽的必要性，项目符合流域综合规划要求。',
                'feasibilityStudyDetails' => '由中国水利水电科学研究院完成可行性研究报告编制。报告对工程地质、水文条件、建设方案、移民安置、环境影响等进行了全面分析。技术方案采用混凝土重力坝，库容5000万立方米。经水利部组织的专家评审，认为方案技术可行、经济合理。',
                'feasibilityStudyOrganization' => '中国水利水电科学研究院',
                'fundingArrangementDetails' => '项目总投资8亿元，其中中央财政补助4亿元，省级财政配套2亿元，市县配套2亿元。资金已列入"十四五"重大水利工程建设规划。',
                'approvalOpinions' => '经省政府审批通过，同意项目立项建设。要求严格执行基本建设程序，落实工程质量责任制，确保按期完成建设任务。',
            ],
            [
                'projectName' => '环境治理信息化平台',
                'startDate' => '2025-11-01',
                'completionDate' => '2026-01-31',
                'organizingUnit' => '市环保局',
                'projectProposalDetails' => '项目建议书提出建设环境监测与治理一体化信息平台，实现环境数据实时采集、智能分析和治理资源优化调度。',
                'feasibilityStudyDetails' => '由浙江省环境监测工程技术中心编制可研报告。方案设计部署100个空气质量监测站、50个水质监测点，建设统一的数据中心和智能分析平台。技术方案采用物联网、大数据、人工智能等先进技术。经专家论证，技术先进、方案可行。',
                'feasibilityStudyOrganization' => '浙江省环境监测工程技术中心',
                'fundingArrangementDetails' => '项目总投资420万元，由市财政全额安排，已列入智慧城市建设专项资金计划。',
                'approvalOpinions' => '市政府批准实施该项目，要求加快建设进度，尽快形成环境监测治理能力，为改善环境质量提供技术支撑。',
            ],
            [
                'projectName' => '智慧医疗信息系统升级',
                'startDate' => '2025-12-01',
                'completionDate' => '2026-02-15',
                'organizingUnit' => '市卫健委',
                'projectProposalDetails' => '项目建议书分析了现有医疗信息系统存在的问题，提出升级改造的必要性和紧迫性，得到市政府支持。',
                'feasibilityStudyDetails' => '由四川大学华西医院信息中心编制可研报告。升级方案包括电子病历系统、智能诊疗辅助系统、远程会诊平台等模块。覆盖全市10家三甲医院和50家社区医院，接入医疗设备2000台。技术方案符合国家卫生信息化标准。',
                'feasibilityStudyOrganization' => '四川大学华西医院信息中心',
                'fundingArrangementDetails' => '项目总投资680万元，由市级财政专项资金安排，纳入2026年度卫生健康领域重点项目。',
                'approvalOpinions' => '经市政府批准同意实施。要求项目建设过程中注重信息安全，保护患者隐私，确保系统稳定可靠运行。',
            ],
            [
                'projectName' => '轨道交通信号系统改造',
                'startDate' => '2025-09-15',
                'completionDate' => '2026-03-01',
                'organizingUnit' => '市地铁集团',
                'projectProposalDetails' => '项目建议书针对现有地铁信号系统老化、运能不足的问题，提出升级改造的方案建议。',
                'feasibilityStudyDetails' => '由中国铁道科学研究院编制可研报告。改造方案采用CBTC（基于通信的列车控制）技术，覆盖3条地铁线路、60个车站、300套信号设备。新系统可提高运行密度、缩短发车间隔，提升运营安全性和效率。技术方案经铁路总公司审查通过。',
                'feasibilityStudyOrganization' => '中国铁道科学研究院',
                'fundingArrangementDetails' => '项目总投资1500万元，由市财政和地铁集团共同筹措，其中市财政补助800万元，地铁集团自筹700万元。',
                'approvalOpinions' => '市政府批准项目实施。要求合理安排施工时间，减少对地铁运营的影响，确保改造期间运营安全。',
            ],
        ];

        foreach ($preliminaryDecisions as $data) {
            // Find the project by name
            $project = $projectRepo->findOneBy(['projectName' => $data['projectName']]);

            if (!$project) {
                continue; // Skip if project not found
            }

            $preliminaryDecision = new PreliminaryDecision();
            $preliminaryDecision->setProject($project);
            $preliminaryDecision->setStartDate(new \DateTimeImmutable($data['startDate']));
            $preliminaryDecision->setCompletionDate(new \DateTimeImmutable($data['completionDate']));
            $preliminaryDecision->setOrganizingUnit($data['organizingUnit']);
            $preliminaryDecision->setProjectProposalDetails($data['projectProposalDetails']);
            $preliminaryDecision->setFeasibilityStudyDetails($data['feasibilityStudyDetails']);
            $preliminaryDecision->setFeasibilityStudyOrganization($data['feasibilityStudyOrganization']);
            $preliminaryDecision->setFundingArrangementDetails($data['fundingArrangementDetails']);
            $preliminaryDecision->setApprovalOpinions($data['approvalOpinions']);

            $manager->persist($preliminaryDecision);
        }

        $manager->flush();
    }
}
