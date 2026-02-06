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
