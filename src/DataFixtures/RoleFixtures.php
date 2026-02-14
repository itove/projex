<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture implements DependentFixtureInterface
{
    public const ROLE_REFERENCE_PREFIX = 'role_';

    public function getDependencies(): array
    {
        return [PermissionFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Project Manager Role (项目管理员/登记方)
        $projectManager = new Role();
        $projectManager->setRoleCode('project_manager');
        $projectManager->setRoleName('项目管理员');
        $projectManager->setRoleDescription('负责项目登记、文档上传、查询自己登记的项目进度与文档审核状态');
        $projectManager->setIsPreset(true);

        // Assign permissions for Project Manager
        $projectManagerPermissions = [
            'basic_login', 'basic_personal_info', 'basic_notifications_view', 'basic_notifications_manage',
            'project_base_create', 'project_base_edit', 'project_flow_register',
            'project_doc_upload', 'project_doc_preview', 'project_doc_delete', 'project_doc_version',
            'project_supplement',
            'homepage_view_own', 'homepage_filter_search', 'homepage_quick_actions',
            'homepage_exception_alerts', 'homepage_data_overview', 'homepage_new_project',
            'query_project_own', 'query_flow_progress', 'query_doc', 'query_doc_download',
            'query_standard_flow', 'query_material_template', 'query_export', 'query_print',
            'stats_view_own',
        ];

        foreach ($projectManagerPermissions as $permCode) {
            /** @var \App\Entity\Permission $permission */
            $permission = $this->getReference(PermissionFixtures::PERMISSION_REFERENCE_PREFIX . $permCode, \App\Entity\Permission::class);
            $projectManager->addPermission($permission);
        }

        $manager->persist($projectManager);
        $this->addReference(self::ROLE_REFERENCE_PREFIX . 'project_manager', $projectManager);

        // 2. Auditor Role (审核人员)
        $auditor = new Role();
        $auditor->setRoleCode('auditor');
        $auditor->setRoleName('审核人员');
        $auditor->setRoleDescription('负责审核项目信息真实性、文档完整性与合规性，反馈审核意见，查询审核记录');
        $auditor->setIsPreset(true);

        // Assign permissions for Auditor
        $auditorPermissions = [
            'basic_login', 'basic_personal_info', 'basic_notifications_view', 'basic_notifications_manage',
            'project_doc_preview',
            'homepage_filter_search', 'homepage_exception_alerts', 'homepage_data_overview',
            'flow_audit', 'flow_audit_opinion', 'flow_audit_record_view',
            'query_project_own', 'query_flow_progress', 'query_doc', 'query_doc_download',
            'query_standard_flow', 'query_material_template', 'query_export', 'query_print',
            'stats_view_own',
        ];

        foreach ($auditorPermissions as $permCode) {
            /** @var \App\Entity\Permission $permission */
            $permission = $this->getReference(PermissionFixtures::PERMISSION_REFERENCE_PREFIX . $permCode, \App\Entity\Permission::class);
            $auditor->addPermission($permission);
        }

        $manager->persist($auditor);
        $this->addReference(self::ROLE_REFERENCE_PREFIX . 'auditor', $auditor);

        // 3. Supervisor Role (监管人员)
        $supervisor = new Role();
        $supervisor->setRoleCode('supervisor');
        $supervisor->setRoleName('监管人员');
        $supervisor->setRoleDescription('监控项目全流程进度，核查各阶段合规性，查询项目档案，查看数据统计');
        $supervisor->setIsPreset(true);

        // Assign permissions for Supervisor (all except system management)
        $supervisorPermissions = [
            'basic_login', 'basic_personal_info', 'basic_notifications_view', 'basic_notifications_manage',
            'project_doc_preview',
            'homepage_view_all', 'homepage_filter_search', 'homepage_quick_actions',
            'homepage_exception_alerts', 'homepage_data_overview',
            'flow_audit_record_view', 'flow_monitor', 'flow_timeout_alert',
            'query_project_all', 'query_flow_progress', 'query_doc', 'query_doc_download',
            'query_standard_flow', 'query_material_template', 'query_export', 'query_print',
            'stats_view_all', 'stats_export', 'stats_custom',
        ];

        foreach ($supervisorPermissions as $permCode) {
            /** @var \App\Entity\Permission $permission */
            $permission = $this->getReference(PermissionFixtures::PERMISSION_REFERENCE_PREFIX . $permCode, \App\Entity\Permission::class);
            $supervisor->addPermission($permission);
        }

        $manager->persist($supervisor);
        $this->addReference(self::ROLE_REFERENCE_PREFIX . 'supervisor', $supervisor);

        // 4. System Admin Role (系统管理员)
        $sysAdmin = new Role();
        $sysAdmin->setRoleCode('system_admin');
        $sysAdmin->setRoleName('系统管理员');
        $sysAdmin->setRoleDescription('维护系统配置、管理用户权限、处理系统异常、备份数据，拥有所有系统权限');
        $sysAdmin->setIsPreset(true);

        // Assign ALL permissions for System Admin
        $allPermissionCodes = [
            'basic_login', 'basic_register', 'basic_password_recovery', 'basic_personal_info',
            'basic_notifications_view', 'basic_notifications_manage',
            'project_base_create', 'project_base_edit', 'project_flow_register',
            'project_doc_upload', 'project_doc_preview', 'project_doc_delete', 'project_doc_version',
            'project_supplement', 'project_terminate', 'project_flow_simplify',
            'homepage_view_own', 'homepage_view_all', 'homepage_filter_search', 'homepage_quick_actions',
            'homepage_exception_alerts', 'homepage_data_overview', 'homepage_new_project',
            'flow_audit', 'flow_audit_opinion', 'flow_audit_record_view', 'flow_allocate',
            'flow_timeout_alert', 'flow_recall_approve', 'flow_monitor',
            'query_project_own', 'query_project_all', 'query_flow_progress', 'query_doc', 'query_doc_download',
            'query_standard_flow', 'query_material_template', 'query_export', 'query_print',
            'stats_view_own', 'stats_view_all', 'stats_export', 'stats_custom',
            'system_config', 'system_backup', 'system_restore', 'system_exception_handle',
            'system_doc_storage', 'system_log_view', 'system_user_manage', 'system_role_manage', 'system_permission_manage',
        ];

        foreach ($allPermissionCodes as $permCode) {
            /** @var \App\Entity\Permission $permission */
            $permission = $this->getReference(PermissionFixtures::PERMISSION_REFERENCE_PREFIX . $permCode, \App\Entity\Permission::class);
            $sysAdmin->addPermission($permission);
        }

        $manager->persist($sysAdmin);
        $this->addReference(self::ROLE_REFERENCE_PREFIX . 'system_admin', $sysAdmin);

        $manager->flush();
    }
}
