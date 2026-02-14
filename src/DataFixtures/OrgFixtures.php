<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Org;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrgFixtures extends Fixture
{
    public const ORG_REFERENCE_PREFIX = 'org_';

    public function load(ObjectManager $manager): void
    {
        $organizations = [
            [
                'name' => '市政工程建设管理集团有限公司',
                'orgCode' => 'ORG-SZGC-001',
                'description' => '主要负责城市基础设施建设、市政道路工程、桥梁隧道建设等大型市政工程项目的投资、建设和管理，具有市政公用工程施工总承包特级资质',
                'contactPerson' => '张建国',
                'contactPhone' => '13901234567',
                'address' => '北京市朝阳区建国路88号市政大厦',
            ],
            [
                'name' => '智慧城市科技发展有限公司',
                'orgCode' => 'ORG-ZHCS-002',
                'description' => '专注于智慧城市信息化建设、大数据平台开发、物联网系统集成，为政府和企业提供智慧城市整体解决方案',
                'contactPerson' => '李明',
                'contactPhone' => '13802345678',
                'address' => '上海市浦东新区张江高科技园区科技路168号',
            ],
            [
                'name' => '绿色能源投资建设集团',
                'orgCode' => 'ORG-LSNY-003',
                'description' => '专业从事太阳能、风能等新能源项目的投资建设，承担国家级绿色能源示范工程，推动清洁能源产业发展',
                'contactPerson' => '王芳',
                'contactPhone' => '13703456789',
                'address' => '江苏省南京市江宁区能源大道258号',
            ],
            [
                'name' => '综合交通枢纽开发有限公司',
                'orgCode' => 'ORG-JTSH-004',
                'description' => '承接综合交通枢纽、轨道交通站点、公路铁路联运中心等大型交通基础设施建设项目，打造现代化综合交通体系',
                'contactPerson' => '赵强',
                'contactPhone' => '13604567890',
                'address' => '广东省广州市天河区交通路128号',
            ],
            [
                'name' => '生态环保工程股份有限公司',
                'orgCode' => 'ORG-STHB-005',
                'description' => '专注于污水处理、垃圾处理、环境治理等生态环保工程建设，致力于改善城市生态环境质量',
                'contactPerson' => '刘洁',
                'contactPhone' => '13505678901',
                'address' => '浙江省杭州市滨江区环保大道368号',
            ],
            [
                'name' => '水利水电建设工程局',
                'orgCode' => 'ORG-SLSD-006',
                'description' => '承担大型水利水电工程、防洪排涝工程、水资源综合利用项目建设，保障区域水资源安全',
                'contactPerson' => '陈浩',
                'contactPhone' => '13406789012',
                'address' => '湖北省武汉市洪山区水务街888号',
            ],
            [
                'name' => '城市综合开发建设集团',
                'orgCode' => 'ORG-CSZH-007',
                'description' => '从事城市综合体、住宅小区、商业地产开发建设，参与城市更新改造、旧城改造等大型城建项目',
                'contactPerson' => '孙伟',
                'contactPhone' => '13307890123',
                'address' => '四川省成都市高新区开发大道168号',
            ],
            [
                'name' => '教育医疗设施建设管理公司',
                'orgCode' => 'ORG-JYYL-008',
                'description' => '专业从事学校、医院、文化体育设施等公共服务设施建设，提升区域教育医疗服务水平',
                'contactPerson' => '周敏',
                'contactPhone' => '13208901234',
                'address' => '陕西省西安市雁塔区教育路288号',
            ],
            [
                'name' => '工业园区基础设施投资公司',
                'orgCode' => 'ORG-GYYQ-009',
                'description' => '负责工业园区、产业园区、高新技术园区基础设施配套建设，打造现代化产业发展平台',
                'contactPerson' => '郑涛',
                'contactPhone' => '13109012345',
                'address' => '安徽省合肥市蜀山区工业大道518号',
            ],
            [
                'name' => '乡村振兴建设发展有限公司',
                'orgCode' => 'ORG-XCZX-010',
                'description' => '专注于美丽乡村建设、农村基础设施改造、乡村旅游开发，助力乡村振兴战略实施',
                'contactPerson' => '吴秀英',
                'contactPhone' => '13010123456',
                'address' => '河南省郑州市金水区乡村路688号',
            ],
        ];

        foreach ($organizations as $index => $orgData) {
            $org = new Org();
            $org->setName($orgData['name']);
            $org->setOrgCode($orgData['orgCode']);
            $org->setDescription($orgData['description']);
            $org->setContactPerson($orgData['contactPerson']);
            $org->setContactPhone($orgData['contactPhone']);
            $org->setAddress($orgData['address']);

            $manager->persist($org);

            // Store reference for other fixtures
            $this->addReference(self::ORG_REFERENCE_PREFIX . $index, $org);
        }

        $manager->flush();
    }
}
