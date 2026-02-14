<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Permission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PermissionFixtures extends Fixture
{
    public const PERMISSION_REFERENCE_PREFIX = 'permission_';

    public function load(ObjectManager $manager): void
    {
        $permissions = [
            // 1. Basic Module Permissions (基础模块权限)
            ['code' => 'basic_login', 'name' => '登录', 'module' => '基础模块', 'operation' => '登录', 'level' => 1],
            ['code' => 'basic_register', 'name' => '注册', 'module' => '基础模块', 'operation' => '注册', 'level' => 1],
            ['code' => 'basic_password_recovery', 'name' => '密码找回', 'module' => '基础模块', 'operation' => '密码找回', 'level' => 1],
            ['code' => 'basic_personal_info', 'name' => '个人信息管理', 'module' => '基础模块', 'operation' => '查看/修改', 'level' => 1],
            ['code' => 'basic_notifications_view', 'name' => '查看消息通知', 'module' => '基础模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'basic_notifications_manage', 'name' => '管理消息通知', 'module' => '基础模块', 'operation' => '标记已读/删除', 'level' => 1],

            // 2. Project Registration Module Permissions (项目登记模块权限)
            ['code' => 'project_base_create', 'name' => '创建项目基础信息', 'module' => '项目登记模块', 'operation' => '新增', 'level' => 1],
            ['code' => 'project_base_edit', 'name' => '修改项目基础信息', 'module' => '项目登记模块', 'operation' => '修改', 'level' => 1],
            ['code' => 'project_flow_register', 'name' => '各阶段流程登记', 'module' => '项目登记模块', 'operation' => '登记', 'level' => 1],
            ['code' => 'project_doc_upload', 'name' => '上传文档', 'module' => '项目登记模块', 'operation' => '上传', 'level' => 1],
            ['code' => 'project_doc_preview', 'name' => '预览文档', 'module' => '项目登记模块', 'operation' => '预览', 'level' => 1],
            ['code' => 'project_doc_delete', 'name' => '删除文档', 'module' => '项目登记模块', 'operation' => '删除', 'level' => 1],
            ['code' => 'project_doc_version', 'name' => '文档版本管理', 'module' => '项目登记模块', 'operation' => '版本替换', 'level' => 1],
            ['code' => 'project_supplement', 'name' => '补充登记', 'module' => '项目登记模块', 'operation' => '补充', 'level' => 1],
            ['code' => 'project_terminate', 'name' => '项目终止登记', 'module' => '项目登记模块', 'operation' => '终止', 'level' => 1],
            ['code' => 'project_flow_simplify', 'name' => '流程简化申请', 'module' => '项目登记模块', 'operation' => '申请', 'level' => 1],

            // 3. Project Display Homepage Module Permissions (项目展示主页模块权限)
            ['code' => 'homepage_view_own', 'name' => '查看自己的项目', 'module' => '项目展示主页模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'homepage_view_all', 'name' => '查看所有项目', 'module' => '项目展示主页模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'homepage_filter_search', 'name' => '筛选/搜索项目', 'module' => '项目展示主页模块', 'operation' => '筛选/搜索', 'level' => 1],
            ['code' => 'homepage_quick_actions', 'name' => '快速操作', 'module' => '项目展示主页模块', 'operation' => '操作', 'level' => 1],
            ['code' => 'homepage_exception_alerts', 'name' => '异常提醒查看', 'module' => '项目展示主页模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'homepage_data_overview', 'name' => '数据概览查看', 'module' => '项目展示主页模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'homepage_new_project', 'name' => '新增项目登记', 'module' => '项目展示主页模块', 'operation' => '新增', 'level' => 1],

            // 4. Flow Management Module Permissions (流程管理模块权限)
            ['code' => 'flow_audit', 'name' => '流程审核', 'module' => '流程管理模块', 'operation' => '审核', 'level' => 1],
            ['code' => 'flow_audit_opinion', 'name' => '审核意见录入', 'module' => '流程管理模块', 'operation' => '录入', 'level' => 1],
            ['code' => 'flow_audit_record_view', 'name' => '审核记录查看', 'module' => '流程管理模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'flow_allocate', 'name' => '流程分配', 'module' => '流程管理模块', 'operation' => '分配', 'level' => 1],
            ['code' => 'flow_timeout_alert', 'name' => '流程超时提醒', 'module' => '流程管理模块', 'operation' => '提醒', 'level' => 1],
            ['code' => 'flow_recall_approve', 'name' => '流程撤回审批', 'module' => '流程管理模块', 'operation' => '审批', 'level' => 1],
            ['code' => 'flow_monitor', 'name' => '流程监控', 'module' => '流程管理模块', 'operation' => '监控', 'level' => 1],

            // 5. Query Module Permissions (信息查询模块权限)
            ['code' => 'query_project_own', 'name' => '查询自己的项目', 'module' => '信息查询模块', 'operation' => '查询', 'level' => 1],
            ['code' => 'query_project_all', 'name' => '查询所有项目', 'module' => '信息查询模块', 'operation' => '查询', 'level' => 1],
            ['code' => 'query_flow_progress', 'name' => '流程进度查询', 'module' => '信息查询模块', 'operation' => '查询', 'level' => 1],
            ['code' => 'query_doc', 'name' => '文档查询', 'module' => '信息查询模块', 'operation' => '查询', 'level' => 1],
            ['code' => 'query_doc_download', 'name' => '文档下载', 'module' => '信息查询模块', 'operation' => '下载', 'level' => 1],
            ['code' => 'query_standard_flow', 'name' => '标准流程查询', 'module' => '信息查询模块', 'operation' => '查询', 'level' => 1],
            ['code' => 'query_material_template', 'name' => '材料模板查询', 'module' => '信息查询模块', 'operation' => '查询', 'level' => 1],
            ['code' => 'query_export', 'name' => '查询结果导出', 'module' => '信息查询模块', 'operation' => '导出', 'level' => 1],
            ['code' => 'query_print', 'name' => '查询结果打印', 'module' => '信息查询模块', 'operation' => '打印', 'level' => 1],

            // 6. Statistics Module Permissions (数据统计模块权限)
            ['code' => 'stats_view_own', 'name' => '查看个人统计数据', 'module' => '数据统计模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'stats_view_all', 'name' => '查看所有统计数据', 'module' => '数据统计模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'stats_export', 'name' => '统计数据导出', 'module' => '数据统计模块', 'operation' => '导出', 'level' => 1],
            ['code' => 'stats_custom', 'name' => '自定义统计条件', 'module' => '数据统计模块', 'operation' => '自定义', 'level' => 1],

            // 7. System Management Module Permissions (系统管理模块权限)
            ['code' => 'system_config', 'name' => '系统配置', 'module' => '系统管理模块', 'operation' => '配置', 'level' => 1],
            ['code' => 'system_backup', 'name' => '数据备份', 'module' => '系统管理模块', 'operation' => '备份', 'level' => 1],
            ['code' => 'system_restore', 'name' => '数据恢复', 'module' => '系统管理模块', 'operation' => '恢复', 'level' => 1],
            ['code' => 'system_exception_handle', 'name' => '异常处理', 'module' => '系统管理模块', 'operation' => '处理', 'level' => 1],
            ['code' => 'system_doc_storage', 'name' => '文档存储管理', 'module' => '系统管理模块', 'operation' => '管理', 'level' => 1],
            ['code' => 'system_log_view', 'name' => '日志管理', 'module' => '系统管理模块', 'operation' => '查看', 'level' => 1],
            ['code' => 'system_user_manage', 'name' => '用户管理', 'module' => '系统管理模块', 'operation' => '管理', 'level' => 1],
            ['code' => 'system_role_manage', 'name' => '角色管理', 'module' => '系统管理模块', 'operation' => '管理', 'level' => 1],
            ['code' => 'system_permission_manage', 'name' => '权限管理', 'module' => '系统管理模块', 'operation' => '管理', 'level' => 1],
        ];

        foreach ($permissions as $index => $permData) {
            $permission = new Permission();
            $permission->setPermissionCode($permData['code']);
            $permission->setPermissionName($permData['name']);
            $permission->setModule($permData['module']);
            $permission->setOperationType($permData['operation']);
            $permission->setPermissionLevel($permData['level']);

            $manager->persist($permission);

            // Store reference for RoleFixtures
            $this->addReference(self::PERMISSION_REFERENCE_PREFIX . $permData['code'], $permission);
        }

        $manager->flush();
    }
}
