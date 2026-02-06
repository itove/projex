<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ProjectSubtype;
use App\Entity\ProjectType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectTypeFixtures extends Fixture
{
    public const CONSTRUCTION_REFERENCE = 'project-type-construction';
    public const INTEGRATION_REFERENCE = 'project-type-integration';

    public function load(ObjectManager $manager): void
    {
        // Create Construction Type
        $construction = new ProjectType();
        $construction->setCode('construction');
        $construction->setName('施工类');
        $construction->setDescription('包括市政工程、建筑工程、水利工程、交通工程等施工类项目');
        $construction->setSortOrder(1);
        $construction->setIsActive(true);
        $manager->persist($construction);
        $this->addReference(self::CONSTRUCTION_REFERENCE, $construction);

        // Create Integration Type
        $integration = new ProjectType();
        $integration->setCode('integration');
        $integration->setName('集成类');
        $integration->setDescription('包括智慧城市、信息化建设、安防系统、环境治理等集成类项目');
        $integration->setSortOrder(2);
        $integration->setIsActive(true);
        $manager->persist($integration);
        $this->addReference(self::INTEGRATION_REFERENCE, $integration);

        // Construction Subtypes
        $constructionSubtypes = [
            ['code' => 'municipal', 'name' => '市政工程', 'order' => 1],
            ['code' => 'building', 'name' => '建筑工程', 'order' => 2],
            ['code' => 'water', 'name' => '水利工程', 'order' => 3],
            ['code' => 'transportation', 'name' => '交通工程', 'order' => 4],
            ['code' => 'other_construction', 'name' => '其他施工类', 'order' => 99],
        ];

        foreach ($constructionSubtypes as $data) {
            $subtype = new ProjectSubtype();
            $subtype->setCode($data['code']);
            $subtype->setName($data['name']);
            $subtype->setProjectType($construction);
            $subtype->setSortOrder($data['order']);
            $subtype->setIsActive(true);
            $manager->persist($subtype);
        }

        // Integration Subtypes
        $integrationSubtypes = [
            ['code' => 'smart_city', 'name' => '智慧城市', 'order' => 1],
            ['code' => 'informatization', 'name' => '信息化建设', 'order' => 2],
            ['code' => 'security', 'name' => '安防系统', 'order' => 3],
            ['code' => 'environmental', 'name' => '环境治理', 'order' => 4],
            ['code' => 'other_integration', 'name' => '其他集成类', 'order' => 99],
        ];

        foreach ($integrationSubtypes as $data) {
            $subtype = new ProjectSubtype();
            $subtype->setCode($data['code']);
            $subtype->setName($data['name']);
            $subtype->setProjectType($integration);
            $subtype->setSortOrder($data['order']);
            $subtype->setIsActive(true);
            $manager->persist($subtype);
        }

        $manager->flush();
    }
}
