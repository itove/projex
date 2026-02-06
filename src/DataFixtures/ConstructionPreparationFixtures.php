<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ConstructionPreparation;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConstructionPreparationFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            PlanningDesignFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $projectRepo = $manager->getRepository(Project::class);

        // Define construction preparations for all 10 projects
        $constructionPreparations = [
            [
                'projectName' => '智慧城市数据平台建设项目',
                'startDate' => '2026-03-01',
                'completionDate' => '2026-03-15',
                'constructionUnit' => '华为技术有限公司',
                'constructionPermitNumber' => '京建施字[2026]00158号',
                'bidDetails' => '采用公开招标方式选定施工单位。共有5家单位投标，经专家评审，华为技术有限公司以技术方案优、报价合理、实施经验丰富中标。中标价格498万元，工期8个月。',
                'contractDetails' => '与中标单位签订施工总承包合同，合同价498万元。合同约定工期8个月，质量标准为合格，质保期2年。付款方式为：合同签订后支付30%，项目完成50%支付40%，验收合格后支付25%，质保期满支付5%。',
                'constructionPlanDetails' => '施工组织设计采用分阶段实施方式。第一阶段进行硬件设备采购和机房建设，第二阶段进行软件平台开发，第三阶段进行系统集成和测试，第四阶段进行试运行和验收。关键控制点包括设备到货时间、软件开发进度、系统集成质量。',
                'qualityControlDetails' => '建立三级质量管理体系：项目经理全面负责，质量总监专职管理，各阶段负责人具体落实。软件开发采用敏捷开发模式，每个迭代进行代码审查。系统集成严格按照国家信息系统集成规范执行，所有设备和软件必须通过验收测试。',
                'safetyPlanDetails' => '信息安全管理按照等级保护三级要求实施。设置专职信息安全员，制定信息安全管理制度。机房建设严格执行安全用电规范，配备消防设施。开发测试环境与生产环境隔离，防止数据泄露。定期进行安全漏洞扫描和渗透测试。',
                'environmentalProtectionDetails' => '机房建设采用节能设备，优化空调系统能效。施工期间合理安排作业时间，减少噪音扰民。废旧设备和包装材料统一回收处理。系统运行采用绿色数据中心标准，降低能源消耗。',
            ],
            [
                'projectName' => '东城区市政道路改造工程',
                'startDate' => '2026-04-01',
                'completionDate' => '2026-04-10',
                'constructionUnit' => '北京市政路桥建设集团有限公司',
                'constructionPermitNumber' => '京建施字[2026]00203号',
                'bidDetails' => '通过公开招标确定施工单位，共有8家单位参与投标。经综合评审，北京市政路桥建设集团有限公司以综合实力强、业绩优良、报价合理中标，中标价格1185万元，工期7个月。',
                'contractDetails' => '签订施工总承包合同，合同价1185万元，工期7个月。合同约定质量标准为优良，安全目标为零事故。付款方式按工程进度拨付，竣工验收合格后留5%质保金，质保期2年。',
                'constructionPlanDetails' => '采用分段施工方式，将15公里道路分为5个施工段，每段3公里，依次施工。每段工期约1.5个月。施工顺序为：交通导改-旧路面铣刨-基层处理-新路面铺设-附属设施安装-交通恢复。重点控制交通组织、施工质量和施工进度。',
                'qualityControlDetails' => '建立健全质量管理体系，配备专职质检员。原材料进场必须检验，不合格材料不得使用。路面压实度、平整度等关键指标严格按规范检测。隐蔽工程必须经监理验收合格后才能进入下道工序。关键工序实行旁站监理。',
                'safetyPlanDetails' => '制定安全生产管理制度和应急预案，设置专职安全员。施工现场设置安全警示标志和防护设施。施工人员必须经过安全教育培训，持证上岗。夜间施工设置充足照明和反光标识。定期进行安全检查，及时消除安全隐患。',
                'environmentalProtectionDetails' => '施工现场设置围挡，减少扬尘。定期洒水降尘，渣土运输车辆加盖篷布。合理安排施工时间，夜间22点后停止噪音较大的作业。施工废料及时清运，分类处理。绿化带保护措施到位，施工完成后及时恢复。',
            ],
            [
                'projectName' => '企业园区智能安防系统',
                'startDate' => '2026-03-01',
                'completionDate' => '2026-03-10',
                'constructionUnit' => '海康威视数字技术股份有限公司',
                'constructionPermitNumber' => '沪建施字[2026]01022号',
                'bidDetails' => '采用邀请招标方式，邀请3家具有安防系统集成资质的单位投标。经技术和商务综合评审，海康威视数字技术股份有限公司中标，中标价格348万元，工期6个月。',
                'contractDetails' => '签订安防系统工程施工合同，合同总价348万元。合同约定工期6个月，系统必须通过公安部门验收。质保期3年，质保期内免费维护。付款方式为：合同签订支付30%，设备到货支付30%，系统调试完成支付30%，验收合格支付10%。',
                'constructionPlanDetails' => '项目实施分为四个阶段：第一阶段进行现场勘察和点位确认；第二阶段进行设备采购和管线施工；第三阶段进行设备安装和调试；第四阶段进行系统联调和试运行。关键控制点包括点位布置合理性、设备质量、系统稳定性。',
                'qualityControlDetails' => '所有设备必须是原厂正品，提供质保承诺。设备到货后进行开箱检验，不合格设备退换。安装过程严格按照施工规范和设计图纸实施。系统调试完成后进行72小时试运行，确保稳定可靠。最终通过公安部门技术验收。',
                'safetyPlanDetails' => '施工人员必须持有电工证等相关资质证书。高空作业采取安全防护措施，使用安全带、安全帽等防护用品。机房施工严格执行用电安全规范，配备灭火器等消防设施。施工期间设置安全警戒区域，非施工人员禁止入内。',
                'environmentalProtectionDetails' => '管线施工产生的废土及时清运，施工现场保持整洁。设备包装材料统一回收处理，不随意丢弃。施工尽量安排在工作日白天进行，避免噪音扰民。施工完成后及时恢复地面和墙面，保持园区环境整洁。',
            ],
            [
                'projectName' => '水利枢纽工程建设',
                'startDate' => '2026-05-01',
                'completionDate' => '2026-06-15',
                'constructionUnit' => '中国水利水电第八工程局有限公司',
                'constructionPermitNumber' => '冀建施字[2026]00456号',
                'bidDetails' => '采用公开招标方式选定施工单位。共有12家具有水利水电施工总承包一级资质的单位投标，经专家评审，中国水利水电第八工程局有限公司中标。中标价格7.85亿元，工期30个月。',
                'contractDetails' => '签订施工总承包合同，合同价7.85亿元，工期30个月。合同约定质量标准为优良，安全目标为零死亡。合同采用固定总价加调价公式模式。按月进度拨付工程款，留10%质保金，质保期3年。',
                'constructionPlanDetails' => '施工组织采用导流-土石方开挖-坝体浇筑-泄洪输水系统施工-金结安装-蓄水验收的顺序。第一年完成导流和基础开挖，第二年完成主坝浇筑，第三年完成附属工程和蓄水验收。关键控制点包括汛期导流、混凝土浇筑质量、工期节点。',
                'qualityControlDetails' => '建立严格的质量管理体系，设置质量总监和专职质检人员。原材料检验、施工过程检验、成品检验三检制度。混凝土配合比试验，浇筑过程全程监控。关键部位采用无损检测手段。隐蔽工程、重要工序实行旁站监理和第三方检测。',
                'safetyPlanDetails' => '建立项目安全生产管理委员会，项目经理为第一责任人。制定专项安全方案，包括高边坡防护、深基坑作业、大型设备使用等。施工人员安全教育全覆盖，特种作业人员持证上岗。配备专职安全员，每日进行安全检查。制定应急预案，定期演练。',
                'environmentalProtectionDetails' => '严格落实环评要求的各项环保措施。施工期间做好水土保持，边坡及时绿化。弃渣场规范管理，防止水土流失。施工废水处理达标后排放。噪音和扬尘控制符合环保要求。做好施工区生态保护，禁止捕猎野生动物。移民安置严格按照规划实施。',
            ],
            [
                'projectName' => '商业综合体建筑工程',
                'startDate' => '2026-06-01',
                'completionDate' => '2026-06-20',
                'constructionUnit' => '中国建筑第二工程局有限公司',
                'constructionPermitNumber' => '深建施字[2026]00678号',
                'bidDetails' => '通过公开招标确定施工总承包单位，共有15家特级资质施工企业参与投标。经严格评审，中国建筑第二工程局有限公司以综合实力最强、施工方案最优中标，中标价格2.45亿元，工期24个月。',
                'contractDetails' => '签订施工总承包合同，合同价2.45亿元，工期24个月。合同约定质量目标为鲁班奖，安全目标为省级安全文明工地。合同采用固定总价模式。按月进度拨付工程款，预留5%质保金，质保期2年。',
                'constructionPlanDetails' => '采用地下室先行、主体结构跟进、幕墙和机电同步、精装修最后的施工流程。基础工程4个月，主体结构12个月，幕墙工程6个月，机电安装8个月，精装修6个月。采用BIM技术进行施工管理，关键工序采用可视化技术交底。',
                'qualityControlDetails' => '建立以创鲁班奖为目标的质量管理体系。原材料和设备必须通过严格检验，不合格产品坚决退场。关键工序样板先行，经验收合格后大面积施工。混凝土、钢筋、幕墙等关键分项工程实行专项方案管理。成立质量攻关小组，解决施工中的质量难题。',
                'safetyPlanDetails' => '建立三级安全管理体系，项目经理为安全第一责任人。制定深基坑、高支模、脚手架、塔吊等危险性较大工程专项安全方案并经专家论证。配备安全管理人员，每日巡查。施工现场实行封闭管理，人员进出实名制。定期组织应急演练，提高应急处置能力。',
                'environmentalProtectionDetails' => '创建绿色施工示范工程。施工现场硬化处理，设置洗车台。土方开挖期间设置雾炮机降尘。建筑垃圾分类收集，资源化利用。施工噪音控制，夜间禁止产生噪音的施工。生活区和施工区分离，生活污水处理达标排放。节约用水用电，循环利用施工用水。',
            ],
            [
                'projectName' => '环境治理信息化平台',
                'startDate' => '2026-03-15',
                'completionDate' => '2026-03-25',
                'constructionUnit' => '浙江大华技术股份有限公司',
                'constructionPermitNumber' => '浙建施字[2026]00298号',
                'bidDetails' => '采用公开招标方式，共有6家单位投标。经专家评审，浙江大华技术股份有限公司以技术方案优、价格合理、实施经验丰富中标，中标价格415万元，工期12个月。',
                'contractDetails' => '签订系统集成合同，合同总价415万元，工期12个月。合同约定系统必须通过环保部门验收，质保期3年。付款方式为：合同签订支付20%，设备采购完成支付30%，安装调试完成支付30%，验收合格支付15%，质保期满支付5%。',
                'constructionPlanDetails' => '施工分四个阶段实施。第一阶段完成监测设备采购和点位勘察，第二阶段完成设备安装和网络建设，第三阶段完成数据中心和软件平台建设，第四阶段完成系统联调和试运行。各阶段任务明确，责任到人，确保按期完成。',
                'qualityControlDetails' => '监测设备必须符合国家环境监测标准，提供计量检定证书。设备安装严格按照技术规范实施，确保数据准确可靠。网络系统保证稳定可靠，数据传输不丢失。软件平台功能完善，界面友好，操作便捷。系统试运行期间进行数据比对，确保监测数据准确性。',
                'safetyPlanDetails' => '监测站点安装施工注意用电安全和高空作业安全。数据中心建设严格执行机房建设规范，配备消防设施和UPS电源。信息系统安全按照等级保护要求实施，防止数据泄露和网络攻击。制定应急预案，确保系统稳定运行。',
                'environmentalProtectionDetails' => '监测设备安装对环境影响小，施工过程注意不破坏周边环境。设备包装材料回收处理。数据中心采用节能设备，降低能耗。系统运行采用绿色机房标准，减少碳排放。',
            ],
            [
                'projectName' => '高速公路交通工程',
                'startDate' => '2026-07-01',
                'completionDate' => '2026-08-15',
                'constructionUnit' => '中国交通建设集团有限公司',
                'constructionPermitNumber' => '鲁建施字[2026]00892号',
                'bidDetails' => '通过公开招标确定施工单位，共有10家特级资质企业参与投标。经专家综合评审，中国交通建设集团有限公司以施工方案科学、管理经验丰富、报价合理中标，中标价格44.5亿元，工期36个月。',
                'contractDetails' => '签订施工总承包合同，合同价44.5亿元，工期36个月。合同约定质量标准为优良，安全目标为"平安工地"。合同采用单价合同模式，按实际工程量结算。按月进度拨付工程款，留10%质保金，质保期3年。',
                'constructionPlanDetails' => '采用分段施工、流水作业的方式。将120公里分为8个施工段，每段15公里，配备8个施工队伍同时施工。施工顺序为：测量放线-路基工程-路面基层-路面面层-桥涵工程-隧道工程-交通工程-绿化工程。第一年完成路基桥涵，第二年完成路面和隧道，第三年完成交通工程和验收。',
                'qualityControlDetails' => '建立全面质量管理体系，实行标准化施工。试验室配备齐全，原材料和成品严格检验。路基压实、路面平整度等关键指标全程监控。桥梁和隧道工程实行专项质量控制。采用先进检测设备，确保工程质量。重要工序实行首件工程制，样板引路。',
                'safetyPlanDetails' => '建立健全安全生产责任制，项目经理为第一责任人。制定桥梁、隧道、高边坡等危险性较大工程专项安全方案。配备安全管理人员，每日安全巡查。施工人员安全培训全覆盖，特种作业持证上岗。配备应急救援设备，制定应急预案，定期演练。交叉施工严格管理，防止相互干扰。',
                'environmentalProtectionDetails' => '严格落实环评要求，做好生态保护。施工便道硬化处理，减少扬尘。边坡及时绿化，防止水土流失。弃土场规范管理。施工废水、生活污水处理达标排放。噪音控制符合要求，经过居民区路段限时施工。野生动物保护措施到位。取土场、弃渣场施工完成后进行生态恢复。',
            ],
            [
                'projectName' => '智慧医疗信息系统升级',
                'startDate' => '2026-03-20',
                'completionDate' => '2026-04-05',
                'constructionUnit' => '东软集团股份有限公司',
                'constructionPermitNumber' => '川建施字[2026]00445号',
                'bidDetails' => '采用竞争性谈判方式选定实施单位，共有4家医疗信息化企业参与。经技术和商务谈判，东软集团股份有限公司以技术实力强、产品成熟、服务优质胜出，成交价格675万元，工期12个月。',
                'contractDetails' => '签订系统建设合同，合同总价675万元，工期12个月。合同约定系统必须符合卫健委电子病历五级标准，通过卫健委评审。质保期3年，质保期内提供免费技术支持和系统升级服务。付款方式按项目进度分期支付。',
                'constructionPlanDetails' => '项目采用分阶段实施方式。第一阶段完成需求调研和系统设计，第二阶段完成软件开发和单元测试，第三阶段完成系统集成和接口调试，第四阶段完成试点医院部署，第五阶段完成全面推广和培训，第六阶段完成系统验收。',
                'qualityControlDetails' => '软件开发严格按照CMMI5级标准执行。需求分析充分，系统设计合理。开发过程采用敏捷开发模式，每个迭代进行代码审查和测试。系统集成严格按照接口规范实施，确保数据准确可靠。试点医院运行期间收集反馈，持续优化。系统性能、安全性、稳定性全面测试。',
                'safetyPlanDetails' => '信息安全管理严格按照等级保护三级标准实施。建立信息安全管理制度，设置专职安全员。系统部署采用安全加固措施，防止网络攻击。数据库定期备份，防止数据丢失。医疗数据加密存储和传输，保护患者隐私。建立应急预案，确保系统稳定运行。',
                'environmentalProtectionDetails' => '系统建设主要为软件开发和部署，对环境影响小。数据中心采用节能服务器和存储设备。优化系统架构，降低硬件资源消耗。淘汰的旧设备规范回收处理。推广无纸化办公，减少纸张消耗。',
            ],
            [
                'projectName' => '产业园区基础设施建设',
                'startDate' => '2026-06-20',
                'completionDate' => '2026-07-10',
                'constructionUnit' => '中建三局集团有限公司',
                'constructionPermitNumber' => '鄂建施字[2026]00723号',
                'bidDetails' => '通过公开招标确定施工总承包单位，共有9家一级资质企业投标。经专家评审，中建三局集团有限公司以综合实力强、施工方案合理、报价适中中标，中标价格3450万元，工期12个月。',
                'contractDetails' => '签订施工总承包合同，合同价3450万元，工期12个月。合同约定质量标准为优良，安全目标为零事故。合同采用固定单价模式。按月进度拨付工程款，留5%质保金，质保期2年。',
                'constructionPlanDetails' => '采用各专业协调配合、流水施工的方式。道路工程先行，为其他专业施工创造条件。给排水、电力、通信管线同步施工，避免重复开挖。绿化工程最后实施。施工组织紧凑，合理安排工序，确保工期。重点控制管线综合、交叉施工协调、工程质量。',
                'qualityControlDetails' => '建立健全质量管理体系，实行全过程质量控制。原材料严格检验，不合格材料不得使用。道路工程严格控制压实度、平整度。管网工程严格控制标高、坡度、闭水试验。隐蔽工程必须经验收合格后才能覆盖。各专业工程相互协调，确保整体质量。',
                'safetyPlanDetails' => '建立安全生产责任制，项目经理为第一责任人。制定安全管理制度和应急预案。施工现场设置安全警示标志和防护设施。深基坑作业、管线施工、起重作业等重点部位加强安全管理。施工人员安全教育培训，特种作业持证上岗。定期安全检查，及时消除隐患。',
                'environmentalProtectionDetails' => '施工现场设置围挡，施工道路硬化。定期洒水降尘，减少扬尘污染。渣土运输车辆密闭运输，不遗撒。合理安排施工时间，减少噪音影响。施工废水处理后排放，不污染环境。建筑垃圾分类处理，资源化利用。施工完成后及时绿化，美化环境。',
            ],
            [
                'projectName' => '轨道交通信号系统改造',
                'startDate' => '2026-05-01',
                'completionDate' => '2026-05-20',
                'constructionUnit' => '卡斯柯信号有限公司',
                'constructionPermitNumber' => '粤建施字[2026]00556号',
                'bidDetails' => '采用邀请招标方式，邀请3家具有轨道交通信号系统改造业绩的单位投标。经专家评审，卡斯柯信号有限公司以技术方案最优、系统最先进、实施经验最丰富中标，中标价格1485万元，工期12个月。',
                'contractDetails' => '签订信号系统改造合同，合同总价1485万元，工期12个月。合同约定系统必须通过行业主管部门验收和安全评估。质保期5年，质保期内提供免费维护和技术支持。付款方式为：合同签订支付20%，设备到货支付30%，安装调试完成支付30%，验收合格支付15%，质保期满支付5%。',
                'constructionPlanDetails' => '改造工作采用分线路、分车站实施的方式，每条线路单独改造，避免相互影响。施工安排在运营结束后的夜间和周末进行，白天正常运营。每个车站改造工期约2周。施工顺序为：旧设备拆除-新设备安装-系统调试-联调联试-试运行-正式投用。关键控制点为系统切换和安全测试。',
                'qualityControlDetails' => '信号系统设备必须通过国家认证，满足轨道交通安全标准。设备到货后进行严格检验，不合格设备退换。安装严格按照技术规范和图纸实施。系统调试过程全程记录，各项参数满足设计要求。联调联试充分，覆盖各种运行场景。试运行期间系统稳定运行，无故障。通过第三方安全评估。',
                'safetyPlanDetails' => '制定详细的施工安全方案和应急预案。施工期间严格执行地铁运营安全管理规定。施工人员经过专门培训，熟悉地铁运营特点。施工区域设置隔离防护，防止影响运营。旧系统拆除和新系统安装严格按照程序实施，防止误操作。系统切换制定详细方案，确保平稳过渡。配备应急备用系统，随时应对突发情况。',
                'environmentalProtectionDetails' => '施工主要在地铁车站内部进行，对外部环境影响小。旧设备规范拆除，统一回收处理。新设备包装材料及时清理。施工期间保持车站环境整洁。噪音控制满足地铁运营要求。施工废料分类处理，不随意丢弃。',
            ],
        ];

        foreach ($constructionPreparations as $data) {
            // Find the project by name
            $project = $projectRepo->findOneBy(['projectName' => $data['projectName']]);

            if (!$project) {
                continue; // Skip if project not found
            }

            $constructionPreparation = new ConstructionPreparation();
            $constructionPreparation->setProject($project);
            $constructionPreparation->setStartDate(new \DateTimeImmutable($data['startDate']));
            $constructionPreparation->setCompletionDate(new \DateTimeImmutable($data['completionDate']));
            $constructionPreparation->setConstructionUnit($data['constructionUnit']);
            $constructionPreparation->setConstructionPermitNumber($data['constructionPermitNumber']);
            $constructionPreparation->setBidDetails($data['bidDetails']);
            $constructionPreparation->setContractDetails($data['contractDetails']);
            $constructionPreparation->setConstructionPlanDetails($data['constructionPlanDetails']);
            $constructionPreparation->setQualityControlDetails($data['qualityControlDetails']);
            $constructionPreparation->setSafetyPlanDetails($data['safetyPlanDetails']);
            $constructionPreparation->setEnvironmentalProtectionDetails($data['environmentalProtectionDetails']);

            $manager->persist($constructionPreparation);
        }

        $manager->flush();
    }
}
