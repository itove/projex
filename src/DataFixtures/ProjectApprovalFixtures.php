<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\ProjectApproval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectApprovalFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            PreliminaryDecisionFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $projectRepo = $manager->getRepository(Project::class);

        // Define project approvals for specific projects
        $projectApprovals = [
            [
                'projectName' => '东城区市政道路改造工程',
                'startDate' => '2026-01-20',
                'completionDate' => '2026-03-15',
                'approvingAuthority' => '北京市发展和改革委员会',
                'approvalDocumentNumber' => '京发改投资[2026]058号',
                'investmentApprovalDetails' => '同意实施东城区市政道路改造工程，项目总投资1200万元。要求项目单位严格按照批准的建设内容、建设规模和投资概算组织实施，不得擅自调整。',
                'landUseApprovalDetails' => '项目用地为既有市政道路用地，不新增用地。道路施工期间需做好交通组织和安全防护工作。',
                'environmentalAssessmentDetails' => '经环境影响评价，项目施工期间会产生一定噪音和扬尘，需采取降噪、洒水抑尘等措施。项目实施有利于改善区域环境，环评审批通过。',
                'socialStabilityAssessmentDetails' => '项目经过充分征求沿线居民意见，群众支持度高。施工期间需提前发布公告，做好交通疏导。社会稳定风险评估结论为低风险。',
                'approvalOpinions' => '经研究，同意该项目立项建设。请项目单位抓紧开展初步设计和施工图设计，做好工程招标准备工作，力争按期开工建设。',
            ],
            [
                'projectName' => '水利枢纽工程建设',
                'startDate' => '2026-03-01',
                'completionDate' => '2026-04-20',
                'approvingAuthority' => '国家发展和改革委员会',
                'approvalDocumentNumber' => '发改农经[2026]215号',
                'investmentApprovalDetails' => '同意建设河北省保定市水利枢纽工程，项目总投资8亿元，其中中央预算内投资4亿元，地方配套4亿元。项目建设期3年，要求2028年底前完成竣工验收。',
                'landUseApprovalDetails' => '项目用地1200亩，其中农用地800亩，建设用地400亩。已完成土地预审，农用地转用和征地手续正在办理中。需做好移民安置和土地复垦工作。',
                'environmentalAssessmentDetails' => '项目环境影响报告书已通过生态环境部审查。项目实施将淹没部分土地，需做好生态补偿。水库蓄水后将改善区域生态环境，综合效益显著。要求严格落实环评提出的各项环保措施。',
                'socialStabilityAssessmentDetails' => '项目涉及移民搬迁300户，已完成移民安置规划。通过充分征求移民意见，移民安置补偿标准合理，群众认可度高。社会稳定风险评估结论为可控。',
                'approvalOpinions' => '经审核，同意该项目立项建设。请省发改委督促项目单位加快前期工作进度，尽快完成初步设计审查和用地、环保等专项手续办理，确保项目按期开工。',
            ],
            [
                'projectName' => '环境治理信息化平台',
                'startDate' => '2026-02-01',
                'completionDate' => '2026-02-28',
                'approvingAuthority' => '浙江省发展和改革委员会',
                'approvalDocumentNumber' => '浙发改投资[2026]102号',
                'investmentApprovalDetails' => '同意建设杭州市环境治理信息化平台项目，项目总投资420万元，由市财政全额承担。项目建设期1年，要求2027年3月前完成验收。',
                'landUseApprovalDetails' => '项目不涉及新增建设用地，监测设备安装于既有建筑物和构筑物上。数据中心设在市环保局办公楼内。',
                'environmentalAssessmentDetails' => '项目为环境治理类信息化建设项目，不产生污染物排放。项目实施后将提升环境监测和治理能力，有利于改善环境质量。环评备案通过。',
                'socialStabilityAssessmentDetails' => '项目为政府投资的公益性项目，不涉及征地拆迁和移民安置。项目实施将提升环境管理水平，社会效益显著。社会稳定风险评估结论为低风险。',
                'approvalOpinions' => '经审查，同意该项目立项。请市环保局尽快完成设计方案编制和评审，按规定履行政府采购程序，确保项目按期建成投运。',
            ],
            [
                'projectName' => '智慧城市数据平台建设项目',
                'startDate' => '2026-01-20',
                'completionDate' => '2026-02-28',
                'approvingAuthority' => '市发展和改革委员会',
                'approvalDocumentNumber' => '市发改投资[2026]015号',
                'investmentApprovalDetails' => '同意实施智慧城市数据平台建设项目，项目总投资500万元，由市财政全额安排。项目建设期8个月，要求2026年11月前完成验收并投入运营。',
                'landUseApprovalDetails' => '项目不新增建设用地。IDC机房建设在市政务服务中心大楼内既有机房基础上扩建，面积200平方米。数据采集设备安装在各委办局既有建筑物上。',
                'environmentalAssessmentDetails' => '项目为信息化基础设施建设项目，运行期间主要能耗为电力，不产生工业废水、废气、固体废物等污染物。机房采用节能设备，符合绿色数据中心标准。环评登记表已备案。',
                'socialStabilityAssessmentDetails' => '项目为政府投资的公共服务平台项目，不涉及征地拆迁。项目建成后将提升城市管理智能化水平，改善公共服务质量，社会效益显著。社会稳定风险评估结论为低风险。',
                'approvalOpinions' => '经审查，同意该项目立项建设。请市信息办按照信息化项目管理要求，严格履行建设程序，加强网络安全管理，确保系统安全稳定运行。',
            ],
            [
                'projectName' => '企业园区智能安防系统',
                'startDate' => '2026-01-15',
                'completionDate' => '2026-02-25',
                'approvingAuthority' => '上海市经济和信息化委员会',
                'approvalDocumentNumber' => '沪经信投资[2026]088号',
                'investmentApprovalDetails' => '同意建设企业园区智能安防系统项目，项目总投资350万元，由园区管委会自筹。项目建设期6个月，要求2026年9月前完成建设并通过公安部门验收。',
                'landUseApprovalDetails' => '项目不涉及新增用地。监控设备安装在园区道路、建筑物等既有设施上。监控中心设在园区管理中心大楼内，面积100平方米。',
                'environmentalAssessmentDetails' => '项目为安全防范系统建设项目，施工期间会产生少量施工噪音和建筑垃圾，需按规范处理。运行期间无污染物排放。项目符合环保要求，环评备案通过。',
                'socialStabilityAssessmentDetails' => '项目为园区公共安全设施建设项目，不涉及征地拆迁和移民安置。项目实施后将提升园区安全管理水平，保障企业和员工安全，社会效益良好。社会稳定风险评估结论为低风险。',
                'approvalOpinions' => '经审核，同意该项目立项。请园区管委会加强与公安部门沟通协调，确保系统建设符合技术标准，按期通过验收，切实发挥安全防范作用。',
            ],
            [
                'projectName' => '商业综合体建筑工程',
                'startDate' => '2026-05-20',
                'completionDate' => '2026-06-10',
                'approvingAuthority' => '深圳市发展和改革委员会',
                'approvalDocumentNumber' => '深发改投资[2026]245号',
                'investmentApprovalDetails' => '同意建设福田区中心商业综合体建筑工程，项目总投资2.5亿元，由企业自筹。项目建设期24个月，要求2028年6月前完成竣工验收。',
                'landUseApprovalDetails' => '项目用地面积1.5万平方米，土地性质为商业用地，土地使用权已通过招拍挂方式取得，土地证号：深房地字第8888888号。建设用地规划许可证已办理，证号：深规地[2026]098号。',
                'environmentalAssessmentDetails' => '项目环境影响报告表已编制完成并通过审批。施工期间需采取降噪、抑尘、污水处理等措施。运营期间产生的生活污水接入市政污水管网，生活垃圾由环卫部门统一清运。项目符合环保要求。',
                'socialStabilityAssessmentDetails' => '项目用地为净地，不涉及征地拆迁和移民安置。项目建设得到周边居民支持。项目建成后将完善区域商业配套，提供就业岗位1000个，社会效益显著。社会稳定风险评估结论为低风险。',
                'approvalOpinions' => '经审核，同意该项目立项建设。请项目单位严格执行基本建设程序，落实质量安全责任，确保工程质量达到优良标准，建成后成为城市精品工程。',
            ],
            [
                'projectName' => '高速公路交通工程',
                'startDate' => '2026-07-01',
                'completionDate' => '2026-08-10',
                'approvingAuthority' => '国家发展和改革委员会',
                'approvalDocumentNumber' => '发改基础[2026]568号',
                'investmentApprovalDetails' => '同意建设山东省济南至泰安高速公路工程，项目总投资45亿元，采用BOT模式建设，特许经营期25年。其中中央车购税补助5亿元，省级配套10亿元，社会资本投资30亿元。项目建设期3年，要求2029年8月前建成通车。',
                'landUseApprovalDetails' => '项目用地8000亩，其中农用地6500亩，建设用地1500亩。已完成土地预审，用地预审意见书号：国土资预审字[2026]135号。农用地转用和土地征收手续正在办理。需严格落实耕地占补平衡和土地复垦义务。',
                'environmentalAssessmentDetails' => '项目环境影响报告书已编制完成并通过生态环境部审查，审批文号：环审[2026]78号。项目实施将占用部分林地和农田，需做好生态补偿。施工期间需严格执行水土保持方案，控制扬尘和噪音污染。运营期间做好沿线绿化和环境保护工作。',
                'socialStabilityAssessmentDetails' => '项目建设涉及沿线5个乡镇、20个村庄，需征地拆迁200户。已完成移民安置规划编制，安置补偿标准合理，群众认可度高。通过充分征求沿线群众意见，项目支持率达95%以上。社会稳定风险评估结论为可控。',
                'approvalOpinions' => '经审核，同意该项目立项建设。请省交通运输厅督促项目单位加快前期工作，尽快完成初步设计审查、用地审批、环保验收等手续办理，确保按期开工建设，如期建成通车。',
            ],
            [
                'projectName' => '智慧医疗信息系统升级',
                'startDate' => '2026-02-20',
                'completionDate' => '2026-03-15',
                'approvingAuthority' => '四川省发展和改革委员会',
                'approvalDocumentNumber' => '川发改投资[2026]142号',
                'investmentApprovalDetails' => '同意实施成都市智慧医疗信息系统升级项目，项目总投资680万元，由市财政专项资金安排。项目建设期12个月，要求2027年4月前完成验收并通过卫健委电子病历五级评审。',
                'landUseApprovalDetails' => '项目不涉及新增建设用地。系统软硬件部署在全市10家三甲医院和50家社区医院既有机房内。数据中心设在市卫健委信息中心机房，面积150平方米。',
                'environmentalAssessmentDetails' => '项目为医疗信息化建设项目，主要为软件开发和系统集成，不产生工业污染物。服务器等设备运行产生的热量通过机房空调系统处理。项目符合环保要求，环评登记表已备案。',
                'socialStabilityAssessmentDetails' => '项目为公益性医疗信息化项目，不涉及征地拆迁。项目实施后将提升医疗服务效率和质量，方便患者就医，社会效益显著。通过征求医护人员和患者代表意见，支持率达98%。社会稳定风险评估结论为低风险。',
                'approvalOpinions' => '经审查，同意该项目立项建设。请市卫健委加强项目管理，注重信息安全和患者隐私保护，确保系统稳定可靠运行，切实改善医疗服务水平。',
            ],
            [
                'projectName' => '产业园区基础设施建设',
                'startDate' => '2026-06-15',
                'completionDate' => '2026-07-05',
                'approvingAuthority' => '湖北省发展和改革委员会',
                'approvalDocumentNumber' => '鄂发改投资[2026]328号',
                'investmentApprovalDetails' => '同意建设襄阳市产业园区基础设施工程，项目总投资3500万元，由开发区管委会筹措。项目建设期12个月，要求2027年7月前完成验收并投入使用。',
                'landUseApprovalDetails' => '项目用地为园区规划的市政道路和公共设施用地，面积500亩，土地性质为建设用地，土地使用权属于开发区管委会。建设用地规划许可证已办理，证号：鄂规地[2026]156号。',
                'environmentalAssessmentDetails' => '项目环境影响报告表已编制并通过审批，审批文号：鄂环审[2026]235号。施工期间需采取洒水降尘、噪音控制、污水处理等措施。道路建设采用透水路面，绿化采用本地树种。项目符合环保要求，对环境影响较小。',
                'socialStabilityAssessmentDetails' => '项目用地为园区规划用地，不涉及征地拆迁和移民安置。项目建设将完善园区基础设施，改善投资环境，促进企业入驻和产业发展，社会效益显著。社会稳定风险评估结论为低风险。',
                'approvalOpinions' => '经审核，同意该项目立项建设。请开发区管委会统筹安排施工计划，加强工程质量监管，确保按期建成投用，为园区企业入驻提供良好条件。',
            ],
            [
                'projectName' => '轨道交通信号系统改造',
                'startDate' => '2026-03-05',
                'completionDate' => '2026-04-25',
                'approvingAuthority' => '广东省发展和改革委员会',
                'approvalDocumentNumber' => '粤发改投资[2026]198号',
                'investmentApprovalDetails' => '同意实施广州市轨道交通信号系统改造项目，项目总投资1500万元，由市财政和地铁集团共同筹措，其中市财政补助800万元，地铁集团自筹700万元。项目建设期12个月，要求2027年5月前完成改造并通过安全评估。',
                'landUseApprovalDetails' => '项目为既有地铁线路的信号系统改造项目，不涉及新增建设用地。设备安装在既有地铁车站和轨道区域内。',
                'environmentalAssessmentDetails' => '项目为地铁信号系统技术改造项目，施工在地铁运营结束后的夜间进行，对环境影响较小。旧设备拆除后统一回收处理，新设备能耗更低、更环保。项目符合环保要求，环评备案通过。',
                'socialStabilityAssessmentDetails' => '项目为地铁运营设施改造项目，不涉及征地拆迁。施工安排在夜间和周末进行，不影响白天正常运营。项目实施后将提高运营效率和安全性，改善乘客出行体验，社会效益显著。社会稳定风险评估结论为低风险。',
                'approvalOpinions' => '经审核，同意该项目立项实施。请市地铁集团制定详细施工方案，确保施工期间运营安全，按期完成改造任务，通过行业主管部门验收和安全评估。',
            ],
        ];

        foreach ($projectApprovals as $data) {
            // Find the project by name
            $project = $projectRepo->findOneBy(['projectName' => $data['projectName']]);

            if (!$project) {
                continue; // Skip if project not found
            }

            $projectApproval = new ProjectApproval();
            $projectApproval->setProject($project);
            $projectApproval->setStartDate(new \DateTimeImmutable($data['startDate']));
            $projectApproval->setCompletionDate(new \DateTimeImmutable($data['completionDate']));
            $projectApproval->setApprovingAuthority($data['approvingAuthority']);
            $projectApproval->setApprovalDocumentNumber($data['approvalDocumentNumber']);
            $projectApproval->setInvestmentApprovalDetails($data['investmentApprovalDetails']);
            $projectApproval->setLandUseApprovalDetails($data['landUseApprovalDetails']);
            $projectApproval->setEnvironmentalAssessmentDetails($data['environmentalAssessmentDetails']);
            $projectApproval->setSocialStabilityAssessmentDetails($data['socialStabilityAssessmentDetails']);
            $projectApproval->setApprovalOpinions($data['approvalOpinions']);

            $manager->persist($projectApproval);
        }

        $manager->flush();
    }
}
