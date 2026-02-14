<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function getDependencies(): array
    {
        return [OrgFixtures::class, RoleFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            // System Admins
            [
                'username' => 'admin',
                'name' => '系统管理员',
                'password' => '111',
                'orgIndex' => 0,
                'roleCode' => 'system_admin',
                'phone' => '13901234567',
                'email' => 'admin@projex.com',
                'position' => '系统管理员',
            ],
            [
                'username' => 'tech_admin',
                'name' => '技术管理员',
                'password' => '111',
                'orgIndex' => 1,
                'roleCode' => 'system_admin',
                'phone' => '13902234567',
                'email' => 'tech.admin@projex.com',
                'position' => '技术总监',
            ],

            // Supervisors
            [
                'username' => 'supervisor1',
                'name' => '李监管',
                'password' => '111',
                'orgIndex' => 0,
                'roleCode' => 'supervisor',
                'phone' => '13801234567',
                'email' => 'li.supervisor@projex.com',
                'position' => '监管主管',
            ],
            [
                'username' => 'supervisor2',
                'name' => '王监管',
                'password' => '111',
                'orgIndex' => 1,
                'roleCode' => 'supervisor',
                'phone' => '13802234567',
                'email' => 'wang.supervisor@projex.com',
                'position' => '质量监管员',
            ],
            [
                'username' => 'supervisor3',
                'name' => '赵监管',
                'password' => '111',
                'orgIndex' => 2,
                'roleCode' => 'supervisor',
                'phone' => '13803234567',
                'email' => 'zhao.supervisor@projex.com',
                'position' => '项目监管专员',
            ],

            // Auditors
            [
                'username' => 'auditor1',
                'name' => '张审核',
                'password' => '111',
                'orgIndex' => 0,
                'roleCode' => 'auditor',
                'phone' => '13701234567',
                'email' => 'zhang.auditor@projex.com',
                'position' => '前期决策审核员',
            ],
            [
                'username' => 'auditor2',
                'name' => '刘审核',
                'password' => '111',
                'orgIndex' => 1,
                'roleCode' => 'auditor',
                'phone' => '13702234567',
                'email' => 'liu.auditor@projex.com',
                'position' => '立项审核员',
            ],
            [
                'username' => 'auditor3',
                'name' => '陈审核',
                'password' => '111',
                'orgIndex' => 2,
                'roleCode' => 'auditor',
                'phone' => '13703234567',
                'email' => 'chen.auditor@projex.com',
                'position' => '设计审核员',
            ],
            [
                'username' => 'auditor4',
                'name' => '杨审核',
                'password' => '111',
                'orgIndex' => 3,
                'roleCode' => 'auditor',
                'phone' => '13704234567',
                'email' => 'yang.auditor@projex.com',
                'position' => '施工审核员',
            ],
            [
                'username' => 'auditor5',
                'name' => '周审核',
                'password' => '111',
                'orgIndex' => 4,
                'roleCode' => 'auditor',
                'phone' => '13705234567',
                'email' => 'zhou.auditor@projex.com',
                'position' => '验收审核员',
            ],
            [
                'username' => 'auditor6',
                'name' => '吴审核',
                'password' => '111',
                'orgIndex' => 5,
                'roleCode' => 'auditor',
                'phone' => '13706234567',
                'email' => 'wu.auditor@projex.com',
                'position' => '结算审核员',
            ],

            // Project Managers
            [
                'username' => 'pm_zhang',
                'name' => '张建国',
                'password' => '111',
                'orgIndex' => 0,
                'roleCode' => 'project_manager',
                'phone' => '13601234567',
                'email' => 'zhang.jianguo@projex.com',
                'position' => '项目经理',
            ],
            [
                'username' => 'pm_li',
                'name' => '李明',
                'password' => '111',
                'orgIndex' => 1,
                'roleCode' => 'project_manager',
                'phone' => '13602234567',
                'email' => 'li.ming@projex.com',
                'position' => '项目负责人',
            ],
            [
                'username' => 'pm_wang',
                'name' => '王芳',
                'password' => '111',
                'orgIndex' => 2,
                'roleCode' => 'project_manager',
                'phone' => '13603234567',
                'email' => 'wang.fang@projex.com',
                'position' => '项目主管',
            ],
            [
                'username' => 'pm_zhao',
                'name' => '赵强',
                'password' => '111',
                'orgIndex' => 3,
                'roleCode' => 'project_manager',
                'phone' => '13604234567',
                'email' => 'zhao.qiang@projex.com',
                'position' => '工程部经理',
            ],
            [
                'username' => 'pm_liu',
                'name' => '刘洁',
                'password' => '111',
                'orgIndex' => 4,
                'roleCode' => 'project_manager',
                'phone' => '13605234567',
                'email' => 'liu.jie@projex.com',
                'position' => '建设部主任',
            ],
            [
                'username' => 'pm_chen',
                'name' => '陈浩',
                'password' => '111',
                'orgIndex' => 5,
                'roleCode' => 'project_manager',
                'phone' => '13606234567',
                'email' => 'chen.hao@projex.com',
                'position' => '工程总监',
            ],
            [
                'username' => 'pm_sun',
                'name' => '孙伟',
                'password' => '111',
                'orgIndex' => 6,
                'roleCode' => 'project_manager',
                'phone' => '13607234567',
                'email' => 'sun.wei@projex.com',
                'position' => '项目总工',
            ],
            [
                'username' => 'pm_zhou',
                'name' => '周敏',
                'password' => '111',
                'orgIndex' => 7,
                'roleCode' => 'project_manager',
                'phone' => '13608234567',
                'email' => 'zhou.min@projex.com',
                'position' => '项目经理',
            ],
            [
                'username' => 'pm_zheng',
                'name' => '郑涛',
                'password' => '111',
                'orgIndex' => 8,
                'roleCode' => 'project_manager',
                'phone' => '13609234567',
                'email' => 'zheng.tao@projex.com',
                'position' => '项目负责人',
            ],
            [
                'username' => 'pm_wu',
                'name' => '吴秀英',
                'password' => '111',
                'orgIndex' => 9,
                'roleCode' => 'project_manager',
                'phone' => '13610234567',
                'email' => 'wu.xiuying@projex.com',
                'position' => '建设办主任',
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setUsername($userData['username']);
            $user->setName($userData['name']);
            $user->setPhone($userData['phone']);
            $user->setEmail($userData['email']);
            $user->setPosition($userData['position']);
            $user->setIsActive(true);

            // Hash password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            // Assign organization
            /** @var \App\Entity\Org $org */
            $org = $this->getReference(OrgFixtures::ORG_REFERENCE_PREFIX . $userData['orgIndex'], \App\Entity\Org::class);
            $user->setOrg($org);

            // Assign role
            /** @var \App\Entity\Role $role */
            $role = $this->getReference(RoleFixtures::ROLE_REFERENCE_PREFIX . $userData['roleCode'], \App\Entity\Role::class);
            $user->addUserRole($role);

            // Sync roles for Symfony security
            $user->syncRolesFromUserRoles();

            $manager->persist($user);
        }

        $manager->flush();
    }
}
