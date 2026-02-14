# RBAC Implementation Guide

## What Has Been Implemented

### 1. Database Schema ✅
- **Org Entity**: Organizations with code, description, contact info
- **User Entity**: Extended with org relationship, roles, phone, email, position, login tracking
- **Role Entity**: 4 predefined roles with permission assignments
- **Permission Entity**: 54 permissions across 7 modules
- **Project Entity**: Added `org` (organization), `registeredBy` (user), and `registrantOrganization` (org) relationships

### 2. RBAC Roles and Permissions ✅

#### Role 1: 项目管理员 (Project Manager)
**Description**: 负责项目登记、文档上传、查询自己登记的项目进度与文档审核状态

**Permissions (25)**:
- Basic: login, personal_info, notifications
- Project Registration: create, edit, flow_register, doc_upload/preview/delete/version, supplement
- Homepage: view_own, filter_search, quick_actions, exception_alerts, data_overview, new_project
- Query: project_own, flow_progress, doc, doc_download, standard_flow, material_template, export, print
- Stats: view_own

**Authorization Rules (Section 4.3.4)**:
- ✅ Can ONLY see projects they registered (`registeredBy` = current user)
- ✅ Can view all core fields of their own projects
- ✅ Operation buttons: 继续登记、补充登记、查看详情、文档管理

#### Role 2: 审核人员 (Auditor)
**Description**: 负责审核项目信息真实性、文档完整性与合规性，反馈审核意见

**Permissions (19)**:
- Basic: login, personal_info, notifications
- Project: doc_preview
- Homepage: filter_search, exception_alerts, data_overview
- Flow: audit, audit_opinion, audit_record_view
- Query: project_own, flow_progress, doc, doc_download, standard_flow, material_template, export, print
- Stats: view_own

**Authorization Rules (Section 4.3.4)**:
- ⚠️ Can ONLY see projects they're assigned to audit (needs flow assignment implementation)
- ✅ Can view project core fields and flow documents
- ✅ Operation buttons: 审核项目、查看详情

#### Role 3: 监管人员 (Supervisor)
**Description**: 监控项目全流程进度，核查各阶段合规性，查询项目档案

**Permissions (23)**:
- Basic: login, personal_info, notifications
- Project: doc_preview
- Homepage: view_all, filter_search, quick_actions, exception_alerts, data_overview
- Flow: audit_record_view, monitor, timeout_alert
- Query: project_all, flow_progress, doc, doc_download, standard_flow, material_template, export, print
- Stats: view_all, export, custom

**Authorization Rules (Section 4.3.4)**:
- ✅ Can see ALL projects
- ✅ Can view all core fields
- ✅ Operation buttons: 查看详情、流程监控、文档核查

#### Role 4: 系统管理员 (System Admin)
**Description**: 维护系统配置、管理用户权限、处理系统异常、备份数据

**Permissions (54)**: ALL permissions

**Authorization Rules (Section 4.3.4)**:
- ✅ Can see ALL projects
- ✅ Can view all fields (core + hidden)
- ✅ All operation buttons

### 3. Fixtures ✅
- 10 Organizations with diverse types
- 54 Permissions mapped to design document
- 4 Roles with appropriate permission assignments
- 20 Users (2 admins, 3 supervisors, 6 auditors, 10 project managers)
- 10 Projects assigned to organizations and users

### 4. CRUD Controllers ✅
- OrgCrudController
- UserCrudController
- RoleCrudController
- PermissionCrudController
- Updated ProjectCrudController with org and registeredBy fields

## What Still Needs Implementation

### 1. Query Filtering Based on Role ⚠️

**Project Manager** - Filter by `registeredBy`:
```php
// In ProjectCrudController::createIndexQueryBuilder()
public function createIndexQueryBuilder(
    SearchDto $searchDto,
    EntityDto $entityDto,
    FieldCollection $fields,
    FilterCollection $filters
): QueryBuilder {
    $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

    $user = $this->getUser();
    if ($this->isGranted('ROLE_PROJECT_MANAGER') && !$this->isGranted('ROLE_SUPERVISOR')) {
        // Project managers only see their own projects
        $qb->andWhere('entity.registeredBy = :user')
           ->setParameter('user', $user);
    }

    return $qb;
}
```

**Auditor** - Filter by assigned audits:
```php
// Need to implement audit assignment system first
// Then filter: WHERE project IN (SELECT project_id FROM audit_assignments WHERE user_id = :userId)
```

### 2. Field Visibility Based on Role ⚠️

**Hidden Auxiliary Fields** for non-admins:
```php
// In ProjectCrudController::configureFields()
if (!$this->isGranted('ROLE_SYSTEM_ADMIN')) {
    // Hide auxiliary fields like gid, internalNotes, etc.
}
```

### 3. Operation Button Filtering ⚠️

**Configure Actions** based on role:
```php
public function configureActions(Actions $actions): Actions
{
    $user = $this->getUser();

    // Project Manager specific actions
    if ($this->isGranted('ROLE_PROJECT_MANAGER')) {
        $actions
            ->add(Crud::PAGE_DETAIL, Action::new('continueRegister', '继续登记'))
            ->add(Crud::PAGE_DETAIL, Action::new('supplementRegister', '补充登记'))
            ->add(Crud::PAGE_DETAIL, Action::new('manageDocuments', '文档管理'));
    }

    // Auditor specific actions
    if ($this->isGranted('ROLE_AUDITOR')) {
        $actions->add(Crud::PAGE_DETAIL, Action::new('auditProject', '审核项目'));
    }

    // Supervisor specific actions
    if ($this->isGranted('ROLE_SUPERVISOR')) {
        $actions
            ->add(Crud::PAGE_DETAIL, Action::new('monitorFlow', '流程监控'))
            ->add(Crud::PAGE_DETAIL, Action::new('inspectDocuments', '文档核查'));
    }

    return $actions;
}
```

### 4. Audit Assignment System ⚠️

Need to create:
- `AuditAssignment` entity (project_id, user_id, flow_stage)
- Assignment workflow
- Filter auditor's project list by assignments

### 5. Security Voters ⚠️

Create custom voters for fine-grained access control:

```php
// ProjectVoter.php
class ProjectVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Project;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();
        $project = $subject;

        // System admins can do anything
        if (in_array('ROLE_SYSTEM_ADMIN', $user->getRoles())) {
            return true;
        }

        // Supervisors can view all
        if ($attribute === self::VIEW &&
            in_array('ROLE_SUPERVISOR', $user->getRoles())) {
            return true;
        }

        // Project managers can edit their own projects
        if ($attribute === self::EDIT &&
            $project->getRegisteredBy() === $user) {
            return true;
        }

        return false;
    }
}
```

### 6. Dashboard Filtering ⚠️

Update DashboardData service to:
- Count only projects visible to current user
- Filter statistics by role
- Show role-appropriate widgets

### 7. Form Field Auto-Population ⚠️

**Auto-populate registeredBy** on project creation:
```php
// In ProjectCrudController::createEntity()
public function createEntity(string $entityFqcn): Project
{
    $project = new Project();
    $project->setRegisteredBy($this->getUser());
    $project->setOrg($this->getUser()->getOrg());
    $project->setRegistrantOrganization($this->getUser()->getOrg());
    return $project;
}
```

## Testing Checklist

### Project Manager Tests
- [ ] Can only see projects they registered
- [ ] Cannot see other users' projects
- [ ] Can create new projects
- [ ] Can edit their own projects
- [ ] Can upload/manage documents for their projects
- [ ] Operation buttons: 继续登记、补充登记、查看详情、文档管理

### Auditor Tests
- [ ] Can only see assigned projects (after assignment system)
- [ ] Can view project details
- [ ] Can audit flows
- [ ] Can preview documents
- [ ] Operation buttons: 审核项目、查看详情

### Supervisor Tests
- [ ] Can see ALL projects
- [ ] Can view all core fields
- [ ] Can monitor flows
- [ ] Can inspect documents
- [ ] Operation buttons: 查看详情、流程监控、文档核查

### System Admin Tests
- [ ] Can see ALL projects
- [ ] Can see all fields (including hidden)
- [ ] Can perform all operations
- [ ] Can manage users/roles/permissions

## Next Steps

1. **Immediate Priority**: Implement query filtering in ProjectCrudController
2. **High Priority**: Create security voters for project access control
3. **Medium Priority**: Implement audit assignment system
4. **Medium Priority**: Configure actions based on roles
5. **Low Priority**: Field visibility based on roles
6. **Low Priority**: Dashboard filtering

## Test Credentials

All users have the same password: **111**

- **Admin**: `admin` / `111`
- **Supervisor**: `supervisor1` / `111`
- **Auditor**: `auditor1` / `111`
- **Project Manager**: `pm_zhang` / `111`
