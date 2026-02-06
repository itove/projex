<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PlanningDesign;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlanningDesignFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            ProjectApprovalFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $projectRepo = $manager->getRepository(Project::class);

        // Define planning designs for 10 projects
        $planningDesigns = [
            [
                'projectName' => '智慧城市数据平台建设项目',
                'startDate' => '2026-02-01',
                'completionDate' => '2026-02-28',
                'designUnit' => '中国电子信息产业集团设计院',
                'designDocumentNumber' => 'CEIG-2026-SC-001',
                'preliminaryDesignDetails' => '初步设计方案确定了平台总体架构，采用微服务架构，包含数据采集层、数据存储层、数据分析层和应用服务层。平台支持接入50+个部门系统，日处理数据量100TB。',
                'technicalDesignDetails' => '技术设计采用Kubernetes容器编排，Redis缓存集群，PostgreSQL主从数据库架构。数据分析采用Spark大数据处理框架，实现实时和离线分析能力。',
                'constructionDrawingDetails' => '详细设计包含网络拓扑图、系统部署图、数据库设计文档、接口规范文档等。明确了各子系统的技术实现方案和接口规范。',
                'budgetEstimateDetails' => '设计概算500万元，其中硬件设备200万元，软件开发250万元，系统集成及测试50万元。',
                'designReviewDetails' => '专家评审会议认为设计方案技术先进、架构合理，满足项目需求。建议加强数据安全设计，完善应急预案。',
                'designApprovalDetails' => '市信息化办公室批准该设计方案，要求按照国家信息安全等级保护三级标准进行建设。',
            ],
            [
                'projectName' => '东城区市政道路改造工程',
                'startDate' => '2026-03-20',
                'completionDate' => '2026-03-31',
                'designUnit' => '北京市市政工程设计研究总院',
                'designDocumentNumber' => 'BJMDI-2026-RD-058',
                'preliminaryDesignDetails' => '初步设计确定道路改造范围15公里，路面结构采用沥青混凝土面层，水泥稳定碎石基层。同步实施雨污分流改造和照明系统升级。',
                'technicalDesignDetails' => '技术设计确定路面结构层厚度：4cm沥青混凝土面层，6cm粗粒式沥青混凝土，36cm水泥稳定碎石基层。雨污水管网采用HDPE双壁波纹管。',
                'constructionDrawingDetails' => '施工图设计包含道路平面图、纵断面图、横断面图、路面结构图、管网布置图、照明系统图等。详细标注了各项工程的施工要求和质量标准。',
                'budgetEstimateDetails' => '设计概算1200万元，其中路面工程800万元，雨污水工程250万元，照明工程100万元，其他工程50万元。',
                'designReviewDetails' => '北京市市政工程质量监督站组织专家评审，认为设计深度满足施工要求，技术方案可行。要求加强施工期间交通组织设计。',
                'designApprovalDetails' => '东城区住建委批准施工图设计，要求严格按图施工，确保工程质量。',
            ],
            [
                'projectName' => '企业园区智能安防系统',
                'startDate' => '2026-02-20',
                'completionDate' => '2026-02-28',
                'designUnit' => '上海安防科技设计研究院',
                'designDocumentNumber' => 'SHSD-2026-SEC-003',
                'preliminaryDesignDetails' => '初步设计方案覆盖园区50万平方米，部署AI智能摄像头300个，人脸识别门禁系统50套，周界入侵报警系统1套。',
                'technicalDesignDetails' => '技术方案采用星型网络拓扑，千兆以太网传输，集中式存储。AI分析采用边缘计算+云端分析相结合的方式，支持人脸识别、车牌识别、行为分析等功能。',
                'constructionDrawingDetails' => '施工图包含点位布置图、网络拓扑图、机房布置图、管线布置图等。详细标注了设备型号、安装要求和调试标准。',
                'budgetEstimateDetails' => '设计概算350万元，其中摄像头及配套180万元，门禁系统80万元，网络及存储60万元，软件平台30万元。',
                'designReviewDetails' => '专家组认为设计方案技术成熟，功能完善。建议增加应急广播系统，加强与消防系统的联动。',
                'designApprovalDetails' => '园区管理公司批准设计方案，要求系统符合公安部GA/T 1400标准。',
            ],
            [
                'projectName' => '水利枢纽工程建设',
                'startDate' => '2026-04-25',
                'completionDate' => '2026-05-30',
                'designUnit' => '中国水利水电勘测设计研究院',
                'designDocumentNumber' => 'CWRDC-2026-HY-215',
                'preliminaryDesignDetails' => '初步设计确定主坝为混凝土重力坝，坝高85米，坝顶长度1200米，水库总库容5000万立方米。配套泄洪洞、输水洞、灌溉渠系等工程。',
                'technicalDesignDetails' => '技术设计采用C30混凝土，坝体分段浇筑，设置横缝和纵缝。泄洪洞采用城门洞型断面，输水洞采用圆形断面，均衬砌混凝土。',
                'constructionDrawingDetails' => '施工图设计包含枢纽总平面图、主坝结构图、泄洪系统图、输水系统图、金属结构图等。详细设计了坝基处理、坝体分期施工方案。',
                'budgetEstimateDetails' => '设计概算8亿元，其中主坝工程4.5亿元，泄洪输水工程2亿元，金属结构及设备1亿元，移民安置0.5亿元。',
                'designReviewDetails' => '水利部组织专家审查，认为设计满足防洪、灌溉、发电等综合利用要求。建议优化泄洪方案，加强生态保护措施。',
                'designApprovalDetails' => '国家发改委批准初步设计，要求严格按照水利工程施工规范组织实施。',
            ],
            [
                'projectName' => '商业综合体建筑工程',
                'startDate' => '2026-05-01',
                'completionDate' => '2026-05-31',
                'designUnit' => '中国建筑设计研究院',
                'designDocumentNumber' => 'CADRI-2026-BL-128',
                'preliminaryDesignDetails' => '初步设计方案为地上30层、地下4层的综合体建筑，总建筑面积15万平方米。建筑采用框架-核心筒结构，外立面采用玻璃幕墙。',
                'technicalDesignDetails' => '结构设计采用C40~C60混凝土，HRB400钢筋。抗震设防烈度8度，结构安全等级一级。幕墙系统采用单元式明框玻璃幕墙。',
                'constructionDrawingDetails' => '施工图包含建筑、结构、给排水、暖通、电气、消防等各专业图纸。详细设计了基础、主体结构、幕墙系统、机电系统等。',
                'budgetEstimateDetails' => '设计概算2.5亿元，其中土建工程1.5亿元，幕墙工程0.5亿元，机电工程0.4亿元，精装修0.1亿元。',
                'designReviewDetails' => '深圳市建设工程质量安全监督总站组织施工图审查，各专业审查合格，符合建设要求。',
                'designApprovalDetails' => '深圳市住建局批准施工图设计，核发建设工程规划许可证和施工许可证。',
            ],
            [
                'projectName' => '环境治理信息化平台',
                'startDate' => '2026-03-01',
                'completionDate' => '2026-03-14',
                'designUnit' => '浙江省环境科学设计研究院',
                'designDocumentNumber' => 'ZJESD-2026-ENV-102',
                'preliminaryDesignDetails' => '初步设计方案包含100个空气质量监测站，50个水质监测点，1套数据中心，1套智能分析平台。采用物联网技术实现数据实时采集和传输。',
                'technicalDesignDetails' => '监测设备采用国家标准监测仪器，数据传输采用4G/5G无线通信。数据中心采用双机热备，存储系统采用分布式存储架构。',
                'constructionDrawingDetails' => '详细设计包含监测站点位图、设备安装图、网络拓扑图、机房布置图、系统架构图等。明确了各类设备的技术参数和安装标准。',
                'budgetEstimateDetails' => '设计概算420万元，其中监测设备200万元，网络通信80万元，数据中心100万元，软件平台40万元。',
                'designReviewDetails' => '省生态环境厅组织专家评审，认为设计方案符合环境监测技术规范，满足环境管理需求。',
                'designApprovalDetails' => '市环保局批准设计方案，要求设备选型符合国家环境监测标准。',
            ],
            [
                'projectName' => '高速公路交通工程',
                'startDate' => '2026-06-01',
                'completionDate' => '2026-07-31',
                'designUnit' => '山东省交通规划设计院',
                'designDocumentNumber' => 'SDTDI-2026-HW-305',
                'preliminaryDesignDetails' => '初步设计为双向六车道高速公路，全长120公里，设计速度120km/h。路线走向综合考虑地形、地质、环保等因素，合理布设4处服务区、8处收费站。',
                'technicalDesignDetails' => '路面结构采用沥青混凝土路面，路基宽度34.5米。桥梁工程采用预应力混凝土梁桥，隧道工程采用新奥法施工。',
                'constructionDrawingDetails' => '施工图设计包含路线平面图、纵断面图、路基路面工程图、桥涵工程图、隧道工程图、交通工程图等全套图纸。',
                'budgetEstimateDetails' => '设计概算45亿元，其中路基路面25亿元，桥梁工程12亿元，隧道工程5亿元，交通工程3亿元。',
                'designReviewDetails' => '交通运输部组织设计审查，认为路线方案合理，结构设计安全可靠，符合高速公路设计规范。',
                'designApprovalDetails' => '省交通厅批准施工图设计，要求严格执行工程建设强制性标准。',
            ],
            [
                'projectName' => '智慧医疗信息系统升级',
                'startDate' => '2026-02-20',
                'completionDate' => '2026-03-15',
                'designUnit' => '四川大学华西医院信息中心',
                'designDocumentNumber' => 'SCUH-2026-MED-089',
                'preliminaryDesignDetails' => '初步设计方案包含电子病历系统升级、智能诊疗辅助系统、远程会诊平台建设。覆盖10家三甲医院、50家社区医院，接入医疗设备2000台。',
                'technicalDesignDetails' => '技术架构采用微服务+分布式架构，电子病历系统符合国家卫健委电子病历五级标准。智能诊疗采用深度学习算法，提供辅助诊断功能。',
                'constructionDrawingDetails' => '详细设计包含系统架构图、网络拓扑图、数据库设计、接口规范文档、系统部署方案等。明确了各子系统的功能模块和数据流转。',
                'budgetEstimateDetails' => '设计概算680万元，其中软件开发400万元，硬件设备150万元，系统集成100万元，培训及运维30万元。',
                'designReviewDetails' => '省卫健委组织专家评审，认为设计方案符合国家卫生信息化标准，功能完善，可满足医疗业务需求。',
                'designApprovalDetails' => '市卫健委批准设计方案，要求系统建设符合医疗数据安全和隐私保护相关规定。',
            ],
            [
                'projectName' => '产业园区基础设施建设',
                'startDate' => '2026-05-20',
                'completionDate' => '2026-06-15',
                'designUnit' => '湖北省城乡规划设计研究院',
                'designDocumentNumber' => 'HBUPDI-2026-INF-167',
                'preliminaryDesignDetails' => '初步设计包含园区道路网络20公里，给水管网15公里，排水管网18公里，供电线路12公里，通信光缆15公里。',
                'technicalDesignDetails' => '道路设计为城市次干道标准，路面结构采用沥青混凝土。给排水管网采用PE管和混凝土管。供电采用10kV架空线路和地下电缆相结合。',
                'constructionDrawingDetails' => '施工图包含道路工程图、给排水工程图、电力工程图、通信工程图、绿化工程图等。详细标注了各类管线的平面位置和埋深。',
                'budgetEstimateDetails' => '设计概算3500万元，其中道路工程1500万元，给排水工程800万元，供电工程700万元，通信工程300万元，绿化工程200万元。',
                'designReviewDetails' => '武汉市规划局组织专家评审，认为设计方案符合园区总体规划，各专业设计协调合理。',
                'designApprovalDetails' => '高新区管委会批准施工图设计，要求各类管线施工做好管线综合协调。',
            ],
            [
                'projectName' => '轨道交通信号系统改造',
                'startDate' => '2026-03-05',
                'completionDate' => '2026-04-30',
                'designUnit' => '中国铁道科学研究院',
                'designDocumentNumber' => 'CARS-2026-SIG-428',
                'preliminaryDesignDetails' => '初步设计方案对3条地铁线路进行信号系统升级，更换为CBTC（基于通信的列车控制）系统。涉及60个车站，300套信号设备。',
                'technicalDesignDetails' => '技术方案采用无线通信技术，实现列车与地面的实时双向通信。系统支持移动闭塞，最小行车间隔可缩短至90秒，提升运能30%。',
                'constructionDrawingDetails' => '详细设计包含信号系统架构图、设备布置图、联锁逻辑图、ATO速度曲线图、通信网络图等。明确了各车站设备配置和改造方案。',
                'budgetEstimateDetails' => '设计概算1500万元，其中信号主机及软件600万元，车载设备400万元，通信设备300万元，工程实施200万元。',
                'designReviewDetails' => '中国城市轨道交通协会组织专家评审，认为设计方案技术先进，安全可靠，符合城市轨道交通信号系统设计规范。',
                'designApprovalDetails' => '市地铁集团批准设计方案，要求改造期间确保运营安全，minimize对运营的影响。',
            ],
        ];

        foreach ($planningDesigns as $data) {
            // Find the project by name
            $project = $projectRepo->findOneBy(['projectName' => $data['projectName']]);

            if (!$project) {
                continue; // Skip if project not found
            }

            $planningDesign = new PlanningDesign();
            $planningDesign->setProject($project);
            $planningDesign->setStartDate(new \DateTimeImmutable($data['startDate']));
            $planningDesign->setCompletionDate(new \DateTimeImmutable($data['completionDate']));
            $planningDesign->setDesignUnit($data['designUnit']);
            $planningDesign->setDesignDocumentNumber($data['designDocumentNumber']);
            $planningDesign->setPreliminaryDesignDetails($data['preliminaryDesignDetails']);
            $planningDesign->setTechnicalDesignDetails($data['technicalDesignDetails']);
            $planningDesign->setConstructionDrawingDetails($data['constructionDrawingDetails']);
            $planningDesign->setBudgetEstimateDetails($data['budgetEstimateDetails']);
            $planningDesign->setDesignReviewDetails($data['designReviewDetails']);
            $planningDesign->setDesignApprovalDetails($data['designApprovalDetails']);

            $manager->persist($planningDesign);
        }

        $manager->flush();
    }
}
