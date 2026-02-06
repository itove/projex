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
            [
                'projectName' => '智慧城市数据平台建设项目',
                'startDate' => '2025-09-01',
                'completionDate' => '2026-01-15',
                'organizingUnit' => '市信息化管理办公室',
                'projectProposalDetails' => '项目建议书提出构建全市统一的智慧城市数据平台，整合交通、环保、安防等各领域数据资源，实现数据共享和智能应用。',
                'feasibilityStudyDetails' => '由中国信息通信研究院编制可行性研究报告。方案设计建设A级IDC机房200平方米，部署服务器20台、存储500TB，开发数据采集、存储、分析、可视化四大核心模块。平台支持PB级数据处理，并发用户5000人以上。技术方案经工信部专家组评审通过，认为技术先进、架构合理、安全可靠。',
                'feasibilityStudyOrganization' => '中国信息通信研究院',
                'fundingArrangementDetails' => '项目总投资500万元，纳入市级智慧城市建设专项资金，由市财政全额安排。资金已列入2026年度信息化建设重点项目计划。',
                'approvalOpinions' => '经市政府专题会议研究，同意实施该项目。要求加强数据安全管理，建立数据共享机制，确保平台稳定运行，为智慧城市建设提供坚实支撑。',
            ],
            [
                'projectName' => '企业园区智能安防系统',
                'startDate' => '2025-10-01',
                'completionDate' => '2026-01-10',
                'organizingUnit' => '园区管委会',
                'projectProposalDetails' => '项目建议书针对园区安全管理需求，提出建设全覆盖、智能化、可视化的安防系统，提升园区安全管理水平。',
                'feasibilityStudyDetails' => '由上海市安全技术防范行业协会推荐的专业机构编制可研报告。方案设计安装高清摄像头320个、门禁系统68套、报警探测器156个，建设监控中心1座。系统采用人脸识别、车牌识别、智能分析等先进技术，实现24小时全方位监控。经公安部门技术审查，方案符合安全防范要求。',
                'feasibilityStudyOrganization' => '上海市安全技术防范行业协会',
                'fundingArrangementDetails' => '项目总投资350万元，由园区管委会自筹，已纳入园区2026年度安全生产专项资金计划。',
                'approvalOpinions' => '园区管委会批准实施该项目。要求系统建设符合公安部技术标准，与公安部门实现数据对接，切实提升园区安全防范能力。',
            ],
            [
                'projectName' => '商业综合体建筑工程',
                'startDate' => '2025-07-01',
                'completionDate' => '2026-05-15',
                'organizingUnit' => '深圳市建设局',
                'projectProposalDetails' => '项目建议书提出在市中心商业区建设大型商业综合体，集购物、办公、娱乐于一体，带动区域商业繁荣。',
                'feasibilityStudyDetails' => '由中国建筑设计研究院编制可行性研究报告。项目总建筑面积18万平方米，地下3层地上30层，建筑高度135米。功能布局：地下为停车场和设备用房，裙楼1-6层为商业，塔楼7-30层为写字楼。结构采用框架-核心筒体系，外墙采用玻璃幕墙。项目符合城市规划和建筑设计规范，经专家评审通过。',
                'feasibilityStudyOrganization' => '中国建筑设计研究院',
                'fundingArrangementDetails' => '项目总投资2.5亿元，由开发企业自筹，其中自有资金1亿元，银行贷款1.5亿元。资金来源已落实，融资方案可行。',
                'approvalOpinions' => '经市规划委员会审查，同意该项目立项建设。要求严格按照批准的规划和设计方案实施，确保工程质量达到优良标准，建成后成为城市地标建筑。',
            ],
            [
                'projectName' => '高速公路交通工程',
                'startDate' => '2025-06-01',
                'completionDate' => '2026-06-30',
                'organizingUnit' => '省交通运输厅',
                'projectProposalDetails' => '项目建议书提出建设区域性高速公路，改善山区交通条件，促进经济发展。项目符合国家和省高速公路网规划。',
                'feasibilityStudyDetails' => '由交通运输部公路科学研究院编制可行性研究报告。项目路线全长120公里，设计速度100公里/小时，双向四车道。全线设大中桥梁45座、隧道8座、互通立交5处、服务区2处。工程地质复杂，需要突破多项技术难题。经交通运输部组织的专家评审，认为路线方案合理、技术可行、经济合理。',
                'feasibilityStudyOrganization' => '交通运输部公路科学研究院',
                'fundingArrangementDetails' => '项目总投资45亿元，采用BOT模式建设，由社会资本投资，特许经营期25年。其中中央车购税补助5亿元，省级交通专项资金10亿元，银行贷款和企业自筹30亿元。资金筹措方案已通过审查。',
                'approvalOpinions' => '经省政府审批，同意该项目立项建设。要求严格执行基本建设程序，加强质量安全管理，确保工程质量优良，按期建成通车，发挥投资效益。',
            ],
            [
                'projectName' => '产业园区基础设施建设',
                'startDate' => '2025-11-15',
                'completionDate' => '2026-06-10',
                'organizingUnit' => '开发区管委会',
                'projectProposalDetails' => '项目建议书提出完善产业园区基础设施配套，建设道路、给排水、电力、通信、燃气等市政设施，为企业入驻创造条件。',
                'feasibilityStudyDetails' => '由湖北省城市规划设计研究院编制可行性研究报告。方案设计新建道路10公里、给排水管网各10公里、电力线路15公里、通信管道12公里、燃气管道8公里、绿化面积15万平方米。工程技术方案符合市政工程设计规范，经专家评审通过。',
                'feasibilityStudyOrganization' => '湖北省城市规划设计研究院',
                'fundingArrangementDetails' => '项目总投资3500万元，由开发区管委会筹措，其中土地出让收益返还2000万元，上级专项资金支持1000万元，开发区自筹500万元。资金已落实到位。',
                'approvalOpinions' => '经开发区管委会研究，同意实施该项目。要求统筹规划、分步实施，优先建设主干道路和主要管网，确保按期完工，满足企业入驻需求。',
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
