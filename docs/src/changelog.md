# 更新日志 | 项目全周期智能管理系统

## 版本 1.1.0 - 2026-02-14

#### 权限管理系统 (RBAC) 实施

根据设计文档实现了完整的基于角色的访问控制系统 (Role-Based Access Control)。

##### 新增实体

1. **组织机构 (Org)**
   - 组织名称、组织编码（唯一）
   - 描述、联系人、联系电话、地址
   - 与用户和项目建立关联关系
   - 支持组织层级管理

2. **角色 (Role)**
   - 角色编码、角色名称、角色描述
   - 预设角色标识（不可删除）
   - 多对多关联权限
   - 多对多关联用户

3. **权限 (Permission)**
   - 权限编码（唯一）、权限名称
   - 所属模块、操作类型、权限层级
   - 支持父子权限层级结构
   - 细粒度权限

##### 四大预设角色及权限分配

实现了四个预设角色：

1. **项目管理员 (Project Manager)**
   - **职责**: 负责项目登记、文档上传、查询自己登记的项目进度与文档审核状态
   - **核心权限**:
     - 基础模块: 登录、个人信息管理、消息通知
     - 项目登记: 创建项目、编辑项目、流程登记、文档上传/预览/删除/版本管理、补充登记
     - 项目主页: 查看自己的项目、筛选搜索、快速操作、异常提醒、数据概览、新增项目
     - 信息查询: 查询自己的项目、流程进度、文档查询/下载、标准流程查询、材料模板、导出打印
     - 数据统计: 查看个人统计
   - **访问限制**: 只能查看自己登记的项目 (`registeredBy = 当前用户`)

2. **审核人员 (Auditor)**
   - **职责**: 负责审核项目信息真实性、文档完整性与合规性，反馈审核意见
   - **核心权限**:
     - 基础模块: 登录、个人信息管理、消息通知
     - 项目操作: 文档预览
     - 项目主页: 筛选搜索、异常提醒、数据概览
     - 流程管理: 流程审核、审核意见录入、审核记录查看
     - 信息查询: 查询分配项目、流程进度、文档查询/下载、标准流程查询、材料模板、导出打印
     - 数据统计: 查看个人统计
   - **访问限制**: 仅查看分配审核的项目（待实现审核分配系统）

3. **监管人员 (Supervisor)**
   - **职责**: 监控项目全流程进度，核查各阶段合规性，查询项目档案
   - **核心权限**:
     - 基础模块: 登录、个人信息管理、消息通知
     - 项目操作: 文档预览
     - 项目主页: 查看所有项目、筛选搜索、快速操作、异常提醒、数据概览
     - 流程管理: 审核记录查看、流程监控、超时提醒
     - 信息查询: 查询所有项目、流程进度、文档查询/下载、标准流程查询、材料模板、导出打印
     - 数据统计: 查看所有统计、导出、自定义统计
   - **访问限制**: 可查看所有项目，但不能编辑（除非同时拥有项目管理员角色）

4. **系统管理员 (System Admin)**
   - **职责**: 维护系统配置、管理用户权限、处理系统异常、备份数据
   - **核心权限**: 拥有所有权限，包括：
     - 以上三个角色的所有权限
     - 系统管理: 系统配置、数据备份/恢复、异常处理、文档存储管理、日志管理
     - 权限管理: 用户管理、角色管理、权限管理
   - **访问限制**: 无限制，可查看和操作所有数据

##### 54个细粒度权限详细列表

**1. 基础模块权限 (6个)**
- `basic_login` - 登录
- `basic_register` - 注册
- `basic_password_recovery` - 密码找回
- `basic_personal_info` - 个人信息管理
- `basic_notifications_view` - 查看消息通知
- `basic_notifications_manage` - 管理消息通知

**2. 项目登记模块权限 (10个)**
- `project_base_create` - 创建项目基础信息
- `project_base_edit` - 修改项目基础信息
- `project_flow_register` - 各阶段流程登记
- `project_doc_upload` - 上传文档
- `project_doc_preview` - 预览文档
- `project_doc_delete` - 删除文档
- `project_doc_version` - 文档版本管理
- `project_supplement` - 补充登记
- `project_terminate` - 项目终止登记
- `project_flow_simplify` - 流程简化申请

**3. 项目展示主页模块权限 (7个)**
- `homepage_view_own` - 查看自己的项目
- `homepage_view_all` - 查看所有项目
- `homepage_filter_search` - 筛选/搜索项目
- `homepage_quick_actions` - 快速操作
- `homepage_exception_alerts` - 异常提醒查看
- `homepage_data_overview` - 数据概览查看
- `homepage_new_project` - 新增项目登记

**4. 流程管理模块权限 (7个)**
- `flow_audit` - 流程审核
- `flow_audit_opinion` - 审核意见录入
- `flow_audit_record_view` - 审核记录查看
- `flow_allocate` - 流程分配
- `flow_timeout_alert` - 流程超时提醒
- `flow_recall_approve` - 流程撤回审批
- `flow_monitor` - 流程监控

**5. 信息查询模块权限 (9个)**
- `query_project_own` - 查询自己的项目
- `query_project_all` - 查询所有项目
- `query_flow_progress` - 流程进度查询
- `query_doc` - 文档查询
- `query_doc_download` - 文档下载
- `query_standard_flow` - 标准流程查询
- `query_material_template` - 材料模板查询
- `query_export` - 查询结果导出
- `query_print` - 查询结果打印

**6. 数据统计模块权限 (4个)**
- `stats_view_own` - 查看个人统计数据
- `stats_view_all` - 查看所有统计数据
- `stats_export` - 统计数据导出
- `stats_custom` - 自定义统计条件

**7. 系统管理模块权限 (9个)**
- `system_config` - 系统配置
- `system_backup` - 数据备份
- `system_restore` - 数据恢复
- `system_exception_handle` - 异常处理
- `system_doc_storage` - 文档存储管理
- `system_log_view` - 日志管理
- `system_user_manage` - 用户管理
- `system_role_manage` - 角色管理
- `system_permission_manage` - 权限管理

##### 数据库架构变更

1. **新增数据表**
   - `org` - 组织机构表
   - `role` - 角色表
   - `permission` - 权限表
   - `role_permission` - 角色权限关联表（多对多）
   - `user_role` - 用户角色关联表（多对多）

2. **用户表 (user) 扩展**
   - 新增字段:
     - `org_id` - 所属组织（外键，必填）
     - `phone` - 手机号
     - `email` - 邮箱
     - `position` - 职位
     - `is_active` - 激活状态
     - `last_login_at` - 最后登录时间
     - `last_login_ip` - 最后登录IP
     - `created_at` - 创建时间
     - `updated_at` - 更新时间
   - 修改字段:
     - `name` - 从可选变为必填，长度从50扩展到100
   - 新增关系:
     - 多对一关联 `Org`
     - 多对多关联 `Role`

3. **项目表 (project) 扩展**
   - 新增字段:
     - `org_id` - 所属组织（外键，必填）
     - `registered_by_id` - 登记人（外键关联用户）
     - `registrant_organization_id` - 登记人单位（外键关联组织，替代原文本字段）
   - 删除字段:
     - `registrant_organization` (VARCHAR) - 已改为外键关联
   - 新增索引:
     - `idx_project_org` - 项目组织索引
     - `idx_project_registered_by` - 登记人索引
   - 新增关系:
     - 多对一关联 `Org` (项目所属组织)
     - 多对一关联 `User` (登记人)
     - 多对一关联 `Org` (登记人单位)

4. **组织表 (org) 扩展**
   - 新增字段:
     - `org_code` - 组织编码（唯一）
     - `description` - 组织描述
     - `contact_person` - 联系人
     - `contact_phone` - 联系电话
     - `address` - 地址
     - `updated_at` - 更新时间
   - 修改字段:
     - `name` - 长度从50扩展到255
   - 新增关系:
     - 一对多关联 `User` (组织的用户)
     - 一对多关联 `Project` (组织的项目)

##### 查询过滤实现（第4.3.4节）

根据设计文档第4.3.4节"权限适配"要求，实现了基于角色的项目查询过滤：

1. **项目管理员查询过滤**
   - 实现位置: `ProjectCrudController::createIndexQueryBuilder()`
   - 过滤逻辑: `WHERE project.registeredBy = :currentUser`
   - 效果: 仅展示当前用户登记的项目
   - 状态: ✅ 已实现

2. **监管人员查询过滤**
   - 过滤逻辑: 无过滤（查看所有项目）
   - 操作限制: 可查看详情，但不能编辑/删除（除非同时拥有项目管理员角色）
   - 状态: ✅ 已实现

3. **系统管理员查询过滤**
   - 过滤逻辑: 无过滤（查看所有项目）
   - 操作限制: 无限制，拥有所有操作权限
   - 状态: ✅ 已实现

4. **审核人员查询过滤**
   - 过滤逻辑: 仅展示分配审核的项目
   - 状态: ⚠️ 暂未开放（需先实现审核分配系统）

##### 自动字段填充

实现了项目创建时的自动字段填充功能：

- **触发时机**: 项目管理员新建项目时
- **自动填充字段**:
  - `registeredBy` - 当前登录用户
  - `org` - 当前用户所属组织
  - `registrantOrganization` - 当前用户所属组织
  - `registrantName` - 当前用户姓名
  - `registrantPhone` - 当前用户手机号
- **实现位置**: `ProjectCrudController::createEntity()`
- **状态**: ✅ 已实现

##### 操作权限控制

实现了基于角色的操作按钮权限控制：

1. **删除权限**
   - 仅系统管理员可删除项目
   - 其他角色禁用删除按钮

2. **创建/编辑权限**
   - 项目管理员: 可创建和编辑自己的项目
   - 监管人员: 仅查看（不能创建/编辑）
   - 审核人员: 仅查看（不能创建/编辑）
   - 系统管理员: 无限制

3. **实现位置**: `ProjectCrudController::configureActions()`
4. **状态**: ✅ 已实现

#### 管理界面 (CRUD Controllers)

新增四个权限管理相关的CRUD控制器：

1. **组织机构管理 (OrgCrudController)**
   - 功能: 查看、新增、编辑组织机构
   - 字段: 组织名称、组织编码、描述、联系人、联系电话、地址
   - 菜单位置: 权限管理 → 组织机构

2. **用户管理 (UserCrudController)**
   - 功能: 查看、新增、编辑用户信息
   - 字段: 用户名、姓名、所属组织、角色、手机号、邮箱、职位、激活状态、登录信息
   - 菜单位置: 权限管理 → 用户

3. **角色管理 (RoleCrudController)**
   - 功能: 查看、新增、编辑角色及权限分配
   - 字段: 角色编码、角色名称、角色描述、预设角色标识、关联权限
   - 菜单位置: 权限管理 → 角色

4. **权限管理 (PermissionCrudController)**
   - 功能: 查看权限列表及详情
   - 字段: 权限编码、权限名称、所属模块、操作类型、权限层级、权限描述
   - 菜单位置: 权限管理 → 权限

5. **项目管理 (ProjectCrudController) 更新**
   - 新增字段:
     - `org` - 所属组织（必填，自动完成）
     - `registeredBy` - 登记人（自动填充）
     - `registrantOrganization` - 登记人单位（关联选择，替代文本输入）

#### 测试数据更新

##### 组织机构数据 (10个)

创建了10个多样化的组织机构，涵盖不同行业：

1. 市政工程建设管理集团有限公司 (ORG-SZGC-001)
2. 智慧城市科技发展有限公司 (ORG-ZHCS-002)
3. 绿色能源投资建设集团 (ORG-LSNY-003)
4. 综合交通枢纽开发有限公司 (ORG-JTSH-004)
5. 生态环保工程股份有限公司 (ORG-STHB-005)
6. 水利水电建设工程局 (ORG-SLSD-006)
7. 城市综合开发建设集团 (ORG-CSZH-007)
8. 教育医疗设施建设管理公司 (ORG-JYYL-008)
9. 工业园区基础设施投资公司 (ORG-GYYQ-009)
10. 乡村振兴建设发展有限公司 (ORG-XCZX-010)

##### 用户数据 (20个)

创建了20个测试用户，分布在不同角色和组织：

**系统管理员 (2个)**
- admin - 系统管理员 (市政工程建设管理集团)
- tech_admin - 技术管理员 (智慧城市科技发展)

**监管人员 (3个)**
- supervisor1 - 李监管 (市政工程建设管理集团)
- supervisor2 - 王监管 (智慧城市科技发展)
- supervisor3 - 赵监管 (绿色能源投资建设集团)

**审核人员 (6个)**
- auditor1 - 张审核/前期决策审核员 (市政工程建设管理集团)
- auditor2 - 刘审核/立项审核员 (智慧城市科技发展)
- auditor3 - 陈审核/设计审核员 (绿色能源投资建设集团)
- auditor4 - 杨审核/施工审核员 (综合交通枢纽开发)
- auditor5 - 周审核/验收审核员 (生态环保工程股份)
- auditor6 - 吴审核/结算审核员 (水利水电建设工程局)

**项目管理员 (10个)**
- pm_zhang - 张建国/项目经理 (市政工程建设管理集团)
- pm_li - 李明/项目负责人 (智慧城市科技发展)
- pm_wang - 王芳/项目主管 (绿色能源投资建设集团)
- pm_zhao - 赵强/工程部经理 (综合交通枢纽开发)
- pm_liu - 刘洁/建设部主任 (生态环保工程股份)
- pm_chen - 陈浩/工程总监 (水利水电建设工程局)
- pm_sun - 孙伟/项目总工 (城市综合开发建设集团)
- pm_zhou - 周敏/项目经理 (教育医疗设施建设管理)
- pm_zheng - 郑涛/项目负责人 (工业园区基础设施投资)
- pm_wu - 吴秀英/建设办主任 (乡村振兴建设发展)

**统一密码**: 所有用户密码统一设置为 `111`

##### 项目数据更新

- 所有现有项目已关联到对应的组织机构
- 每个项目自动分配给所属组织的项目管理员作为登记人
- 登记人单位改为组织关联（外键）

### 📝 配置文件更新

#### 国际化翻译

更新 `translations/messages.zh_CN.yaml`，新增翻译：
- `Org`: 组织机构
- `Role`: 角色
- `Permission`: 权限
- `User`: 用户

#### 后台菜单

更新 `DashboardController::configureMenuItems()`，新增"权限管理"菜单区：
- 组织机构 (fa-building)
- 用户 (fa-users)
- 角色 (fa-user-tag)
- 权限 (fa-key)

### 📚 文档更新

新增以下技术文档：

1. **RBAC_IMPLEMENTATION.md** - RBAC实现指南
   - 完整的权限系统架构说明
   - 四大角色详细权限列表
   - 实现状态检查清单
   - 待实现功能列表
   - 测试步骤和凭据

2. **QUERY_FILTERING_IMPLEMENTATION.md** - 查询过滤实现文档
   - 查询过滤逻辑详解
   - 自动字段填充说明
   - 操作权限控制规则
   - 手动测试步骤
   - SQL验证查询
   - 实现检查清单

### 🔧 技术改进

1. **User实体同步机制**
   - 新增 `syncRolesFromUserRoles()` 方法
   - 自动将数据库角色同步到Symfony安全角色
   - 支持多角色权限合并

2. **索引优化**
   - 为 `project.org_id` 添加索引
   - 为 `project.registered_by_id` 添加索引
   - 为 `user.org_id` 添加索引
   - 为 `role.role_code` 添加索引
   - 为 `permission.permission_code` 添加索引
   - 为 `permission.module` 添加索引

3. **数据完整性**
   - 所有外键关系明确定义 `onDelete` 策略
   - Org删除限制（RESTRICT）- 有关联数据时不可删除
   - User删除级联更新（SET NULL）- 删除用户后项目保留
   - 角色权限级联删除（CASCADE）

### ⚠️ 待实现功能

以下功能已规划但尚未实现，将在后续版本中完成：

1. **审核分配系统** (优先级: 高)
   - 创建 `AuditAssignment` 实体
   - 实现审核任务分配流程
   - 启用审核人员的项目查询过滤

2. **安全投票器 (Security Voters)** (优先级: 高)
   - `ProjectVoter` - 项目级别的细粒度访问控制
   - `FlowVoter` - 流程操作权限控制
   - `DocumentVoter` - 文档访问权限控制

3. **角色专属操作按钮** (优先级: 中)
   - 项目管理员: 继续登记、补充登记、文档管理
   - 审核人员: 审核项目
   - 监管人员: 流程监控、文档核查

4. **字段可见性控制** (优先级: 中)
   - 根据角色隐藏辅助字段
   - 系统管理员可见所有字段
   - 其他角色仅可见核心字段

5. **仪表板过滤** (优先级: 低)
   - 根据角色过滤统计数据
   - 显示角色专属的数据小部件
   - 项目数量按权限范围统计

### 🧪 测试

**单元测试**
- 新增 `ProjectManagerAccessTest` - 验证项目管理员访问控制
- 测试用例覆盖:
  - 项目管理员只能查看自己的项目
  - 监管人员可以查看所有项目
  - 系统管理员可以查看所有项目
  - 项目自动字段填充

### 🔄 迁移文件

新增数据库迁移:
1. `Version20260214082505` - RBAC核心表创建
   - 创建 `permission`, `role`, `role_permission`, `user_role` 表
   - 扩展 `org` 表字段
   - 扩展 `user` 表字段
   - 扩展 `project` 表字段

2. `Version20260214084338` - 项目登记人关联
   - 添加 `project.registered_by_id`
   - 将 `project.registrant_organization` 从文本改为外键

### 📊 数据库架构总览

**核心表结构**:
- `org` (组织) → `user` (用户) → `project` (项目)
- `permission` (权限) ← `role_permission` → `role` (角色) ← `user_role` → `user` (用户)

**关系链**:
```
Org (1) ──── (N) User (N) ──── (N) Role (N) ──── (N) Permission
 │                 │
 │                 │
 └─── (N) Project ─┘
       (N)
```

### 🎓 登录凭据

**测试账号** (所有密码: `111`):

| 角色 | 用户名 | 密码 | 组织 |
|------|--------|------|------|
| 系统管理员 | admin | 111 | 市政工程建设管理集团 |
| 系统管理员 | tech_admin | 111 | 智慧城市科技发展 |
| 监管人员 | supervisor1 | 111 | 市政工程建设管理集团 |
| 审核人员 | auditor1 | 111 | 市政工程建设管理集团 |
| 项目管理员 | pm_zhang | 111 | 市政工程建设管理集团 |

### 🔍 验证方法

**1. 验证项目管理员权限**:
```bash
# 使用 pm_zhang 登录
# 访问 /admin → 项目基础信息
# 应仅显示 pm_zhang 登记的项目
```

**2. 验证监管人员权限**:
```bash
# 使用 supervisor1 登录
# 访问 /admin → 项目基础信息
# 应显示所有项目，但无编辑按钮
```

**3. 验证系统管理员权限**:
```bash
# 使用 admin 登录
# 访问 /admin → 权限管理
# 可查看和管理 组织、用户、角色、权限
```

**4. SQL验证查询**:
```sql
-- 查看角色权限分配
SELECT r.role_name, COUNT(p.id) as permission_count
FROM role r
LEFT JOIN role_permission rp ON r.id = rp.role_id
LEFT JOIN permission p ON rp.permission_id = p.id
GROUP BY r.id, r.role_name;

-- 查看用户角色分配
SELECT u.username, u.name, o.name as org_name, r.role_name
FROM "user" u
JOIN org o ON u.org_id = o.id
LEFT JOIN user_role ur ON u.id = ur.user_id
LEFT JOIN role r ON ur.role_id = r.id
ORDER BY u.id;

-- 查看项目登记人分布
SELECT p.project_name, p.project_number,
       u.username as registered_by,
       o.name as org_name
FROM project p
LEFT JOIN "user" u ON p.registered_by_id = u.id
JOIN org o ON p.org_id = o.id
ORDER BY p.id;
```

### 📋 检查清单

权限管理系统实现检查清单:

**数据库层面**:
- [x] 创建 Org, Role, Permission 实体
- [x] 创建多对多关联表 (role_permission, user_role)
- [x] 扩展 User 实体字段
- [x] 扩展 Project 实体字段
- [x] 添加数据库索引
- [x] 执行迁移

**业务逻辑层面**:
- [x] 定义54个细粒度权限
- [x] 创建4个预设角色
- [x] 分配角色权限
- [x] 实现用户角色同步
- [x] 实现项目查询过滤
- [x] 实现自动字段填充
- [x] 实现操作权限控制

**界面层面**:
- [x] OrgCrudController
- [x] UserCrudController
- [x] RoleCrudController
- [x] PermissionCrudController
- [x] 更新 ProjectCrudController
- [x] 更新后台菜单
- [x] 添加翻译

**测试数据**:
- [x] 10个组织机构
- [x] 54个权限
- [x] 4个角色
- [x] 20个用户
- [x] 更新项目关联

**文档**:
- [x] RBAC实现指南
- [x] 查询过滤实现文档
- [x] 变更日志 (本文档)

**待实现** (后续版本):
- [ ] 审核分配系统
- [ ] Security Voters
- [ ] 角色专属操作按钮
- [ ] 字段可见性控制
- [ ] 仪表板过滤

---

## 版本 1.0.0 - 2026-02-06

### 🎉 初始发布

实现了施工/集成类项目全生命周期管理系统的核心功能：

- 项目基础信息管理
- 七大生命周期阶段流程管理
- 文档和图片上传管理
- 项目类型和子类型管理
- 项目展示主页
- 基础数据统计

详细功能请参考设计文档。
