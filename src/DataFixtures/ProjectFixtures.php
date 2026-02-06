<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Project;
use App\Enum\FundingSource;
use App\Enum\ProjectNature;
use App\Enum\ProjectStatus;
use App\Enum\ProjectType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create 10 sample projects
        $projects = [
            [
                'name' => '智慧城市数据平台建设项目',
                'type' => ProjectType::INTEGRATION,
                'subtype' => '智慧城市',
                'industry' => '信息技术',
                'location' => '北京市海淀区',
                'nature' => ProjectNature::GOVERNMENT,
                'leader' => '张伟',
                'phone' => '13800138001',
                'email' => 'zhang.wei@example.com',
                'budget' => '5000000.00',
                'startDate' => '2026-03-01',
                'endDate' => '2027-12-31',
                'purpose' => '建设统一的城市数据平台，整合各部门数据资源，提升城市管理智能化水平。',
                'scale' => '覆盖全市10个区，接入50+个部门系统，预计日处理数据量达100TB。',
                'funding' => FundingSource::GOVERNMENT_FISCAL,
                'registrant' => '李明',
                'organization' => '市信息化办公室',
                'regPhone' => '13900139001',
                'status' => ProjectStatus::DRAFT,
            ],
            [
                'name' => '东城区市政道路改造工程',
                'type' => ProjectType::CONSTRUCTION,
                'subtype' => '市政工程',
                'industry' => '市政建设',
                'location' => '北京市东城区',
                'nature' => ProjectNature::GOVERNMENT,
                'leader' => '王芳',
                'phone' => '13800138002',
                'email' => 'wang.fang@example.com',
                'budget' => '12000000.00',
                'startDate' => '2026-04-01',
                'endDate' => '2026-10-31',
                'purpose' => '改善老城区道路状况，提升交通通行能力，完善市政基础设施。',
                'scale' => '涉及道路总长度15公里，包括路面翻新、雨污分流改造、照明系统升级等。',
                'funding' => FundingSource::GOVERNMENT_FISCAL,
                'registrant' => '刘强',
                'organization' => '东城区住建委',
                'regPhone' => '13900139002',
                'status' => ProjectStatus::REGISTERED,
            ],
            [
                'name' => '企业园区智能安防系统',
                'type' => ProjectType::INTEGRATION,
                'subtype' => '安防系统',
                'industry' => '安防科技',
                'location' => '上海市浦东新区',
                'nature' => ProjectNature::ENTERPRISE,
                'leader' => '陈静',
                'phone' => '13800138003',
                'email' => 'chen.jing@example.com',
                'budget' => '3500000.00',
                'startDate' => '2026-02-15',
                'endDate' => '2026-08-31',
                'purpose' => '建设智能化安防监控系统，提升园区安全管理水平，实现智能预警和响应。',
                'scale' => '覆盖园区面积50万平方米，安装AI摄像头300个，智能门禁50套。',
                'funding' => FundingSource::ENTERPRISE_OWNED,
                'registrant' => '赵敏',
                'organization' => '科技园区管理公司',
                'regPhone' => '13900139003',
                'status' => ProjectStatus::DRAFT,
            ],
            [
                'name' => '水利枢纽工程建设',
                'type' => ProjectType::CONSTRUCTION,
                'subtype' => '水利工程',
                'industry' => '水利建设',
                'location' => '河北省保定市',
                'nature' => ProjectNature::GOVERNMENT,
                'leader' => '孙勇',
                'phone' => '13800138004',
                'email' => 'sun.yong@example.com',
                'budget' => '80000000.00',
                'startDate' => '2026-05-01',
                'endDate' => '2028-12-31',
                'purpose' => '建设区域性水利枢纽，提升防洪能力，保障区域水资源调配。',
                'scale' => '新建水库库容5000万立方米，主坝长度1200米，配套渠系工程30公里。',
                'funding' => FundingSource::GOVERNMENT_FISCAL,
                'registrant' => '周华',
                'organization' => '省水利厅',
                'regPhone' => '13900139004',
                'status' => ProjectStatus::REGISTERED,
            ],
            [
                'name' => '商业综合体建筑工程',
                'type' => ProjectType::CONSTRUCTION,
                'subtype' => '建筑工程',
                'industry' => '房地产开发',
                'location' => '深圳市福田区',
                'nature' => ProjectNature::ENTERPRISE,
                'leader' => '吴磊',
                'phone' => '13800138005',
                'email' => 'wu.lei@example.com',
                'budget' => '250000000.00',
                'startDate' => '2026-06-01',
                'endDate' => '2028-06-30',
                'purpose' => '建设集购物、餐饮、娱乐、办公于一体的现代化商业综合体。',
                'scale' => '总建筑面积15万平方米，地上30层，地下4层，包含商业面积8万平方米。',
                'funding' => FundingSource::BANK_LOAN,
                'registrant' => '郑伟',
                'organization' => '地产开发公司',
                'regPhone' => '13900139005',
                'status' => ProjectStatus::DRAFT,
            ],
            [
                'name' => '环境治理信息化平台',
                'type' => ProjectType::INTEGRATION,
                'subtype' => '环境治理',
                'industry' => '环境保护',
                'location' => '杭州市西湖区',
                'nature' => ProjectNature::GOVERNMENT,
                'leader' => '黄丽',
                'phone' => '13800138006',
                'email' => 'huang.li@example.com',
                'budget' => '4200000.00',
                'startDate' => '2026-03-15',
                'endDate' => '2027-03-14',
                'purpose' => '建设环境监测与治理一体化平台，实时监控环境指标，智能调度治理资源。',
                'scale' => '部署空气质量监测站100个，水质监测点50个，智能分析平台1套。',
                'funding' => FundingSource::GOVERNMENT_FISCAL,
                'registrant' => '钱伟',
                'organization' => '市环保局',
                'regPhone' => '13900139006',
                'status' => ProjectStatus::REGISTERED,
            ],
            [
                'name' => '高速公路交通工程',
                'type' => ProjectType::CONSTRUCTION,
                'subtype' => '交通工程',
                'industry' => '交通运输',
                'location' => '山东省济南市',
                'nature' => ProjectNature::GOVERNMENT,
                'leader' => '林涛',
                'phone' => '13800138007',
                'email' => 'lin.tao@example.com',
                'budget' => '450000000.00',
                'startDate' => '2026-07-01',
                'endDate' => '2029-06-30',
                'purpose' => '建设连接两市的高速公路，改善区域交通状况，促进经济发展。',
                'scale' => '双向六车道高速公路，全长120公里，设服务区4处，收费站8处。',
                'funding' => FundingSource::SOCIAL_CAPITAL,
                'registrant' => '杨丽',
                'organization' => '省交通厅',
                'regPhone' => '13900139007',
                'status' => ProjectStatus::DRAFT,
            ],
            [
                'name' => '智慧医疗信息系统升级',
                'type' => ProjectType::INTEGRATION,
                'subtype' => '信息化建设',
                'industry' => '医疗卫生',
                'location' => '成都市武侯区',
                'nature' => ProjectNature::GOVERNMENT,
                'leader' => '何军',
                'phone' => '13800138008',
                'email' => 'he.jun@example.com',
                'budget' => '6800000.00',
                'startDate' => '2026-04-01',
                'endDate' => '2027-03-31',
                'purpose' => '升级医院信息系统，实现智能诊疗、远程会诊，提升医疗服务水平。',
                'scale' => '覆盖全市三甲医院10家，社区医院50家，接入医疗设备2000台。',
                'funding' => FundingSource::GOVERNMENT_FISCAL,
                'registrant' => '谢敏',
                'organization' => '市卫健委',
                'regPhone' => '13900139008',
                'status' => ProjectStatus::REGISTERED,
            ],
            [
                'name' => '产业园区基础设施建设',
                'type' => ProjectType::CONSTRUCTION,
                'subtype' => '其他施工类',
                'industry' => '园区开发',
                'location' => '武汉市东湖高新区',
                'nature' => ProjectNature::ENTERPRISE,
                'leader' => '冯强',
                'phone' => '13800138009',
                'email' => 'feng.qiang@example.com',
                'budget' => '35000000.00',
                'startDate' => '2026-05-15',
                'endDate' => '2027-05-14',
                'purpose' => '完善产业园区基础设施，包括道路、供水、供电、通信等配套设施建设。',
                'scale' => '园区总面积200万平方米，道路网络20公里，供水管网15公里。',
                'funding' => FundingSource::ENTERPRISE_OWNED,
                'registrant' => '袁华',
                'organization' => '高新区管委会',
                'regPhone' => '13900139009',
                'status' => ProjectStatus::DRAFT,
            ],
            [
                'name' => '轨道交通信号系统改造',
                'type' => ProjectType::INTEGRATION,
                'subtype' => '其他集成类',
                'industry' => '轨道交通',
                'location' => '广州市天河区',
                'nature' => ProjectNature::GOVERNMENT,
                'leader' => '邓敏',
                'phone' => '13800138010',
                'email' => 'deng.min@example.com',
                'budget' => '15000000.00',
                'startDate' => '2026-08-01',
                'endDate' => '2027-07-31',
                'purpose' => '升级地铁信号系统，提升运行安全性和效率，实现智能调度。',
                'scale' => '涉及地铁线路3条，车站60个，信号设备300套。',
                'funding' => FundingSource::GOVERNMENT_FISCAL,
                'registrant' => '崔伟',
                'organization' => '市地铁集团',
                'regPhone' => '13900139010',
                'status' => ProjectStatus::REGISTERED,
            ],
        ];

        foreach ($projects as $index => $data) {
            $project = new Project();
            $project->setProjectName($data['name']);
            $project->setProjectType($data['type']);
            $project->setProjectSubtype($data['subtype']);
            $project->setProjectIndustry($data['industry']);
            $project->setProjectLocation($data['location']);
            $project->setProjectNature($data['nature']);
            $project->setLeaderName($data['leader']);
            $project->setLeaderPhone($data['phone']);
            $project->setLeaderEmail($data['email']);
            $project->setBudget($data['budget']);
            $project->setPlannedStartDate(new \DateTimeImmutable($data['startDate']));
            $project->setPlannedEndDate(new \DateTimeImmutable($data['endDate']));
            $project->setPurpose($data['purpose']);
            $project->setScale($data['scale']);
            $project->setFundingSource($data['funding']);
            $project->setRegistrantName($data['registrant']);
            $project->setRegistrantOrganization($data['organization']);
            $project->setRegistrantPhone($data['regPhone']);
            $project->setStatus($data['status']);

            // Generate project number for REGISTERED projects
            if ($data['status'] === ProjectStatus::REGISTERED) {
                $project->setProjectNumber(sprintf('XM2026%03d', $index + 1));
                $project->setIsCoreLocked(true);
            }

            $manager->persist($project);
        }

        $manager->flush();
    }
}
