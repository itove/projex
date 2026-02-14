# Query Filtering Implementation - Project Manager Access Control

## Overview
Implemented query filtering in `ProjectCrudController` to ensure project managers only see projects they registered, as required by Section 4.3.4 of the design document.

## What Was Implemented

### 1. Query Filtering (`createIndexQueryBuilder`)

**Location**: `src/Controller/Admin/ProjectCrudController.php`

**Logic**:
```php
public function createIndexQueryBuilder(
    SearchDto $searchDto,
    EntityDto $entityDto,
    FieldCollection $fields,
    FilterCollection $filters
): QueryBuilder {
    $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

    $user = $this->getUser();

    // System admins and supervisors can see all projects
    if ($this->isGranted('ROLE_SYSTEM_ADMIN') || $this->isGranted('ROLE_SUPERVISOR')) {
        return $qb; // No filtering
    }

    // Project managers only see projects they registered
    if ($this->isGranted('ROLE_PROJECT_MANAGER')) {
        $qb->andWhere('entity.registeredBy = :user')
           ->setParameter('user', $user);
        return $qb;
    }

    // Auditors - show nothing until assignment system is implemented
    if ($this->isGranted('ROLE_AUDITOR')) {
        $qb->andWhere('1 = 0'); // Show nothing
        return $qb;
    }

    // Default: show nothing
    $qb->andWhere('1 = 0');
    return $qb;
}
```

**Authorization Rules**:
| Role | Access Rule | Implementation Status |
|------|-------------|----------------------|
| **System Admin** | See ALL projects | ✅ No filtering applied |
| **Supervisor** | See ALL projects | ✅ No filtering applied |
| **Project Manager** | Only see own projects | ✅ Filter: `registeredBy = current user` |
| **Auditor** | Only see assigned projects | ⚠️ Blocked (needs assignment system) |

### 2. Auto-Population (`createEntity`)

**Location**: `src/Controller/Admin/ProjectCrudController.php`

When a project manager creates a new project, automatically populate:
- `registeredBy` = current user
- `org` = current user's organization
- `registrantOrganization` = current user's organization
- `registrantName` = current user's name
- `registrantPhone` = current user's phone

```php
public function createEntity(string $entityFqcn): Project
{
    $project = new Project();
    $user = $this->getUser();

    if ($user) {
        $project->setRegisteredBy($user);
        $project->setOrg($user->getOrg());
        $project->setRegistrantOrganization($user->getOrg());
        $project->setRegistrantName($user->getName());
        $project->setRegistrantPhone($user->getPhone() ?? '');
    }

    return $project;
}
```

### 3. Action Permissions (`configureActions`)

**Location**: `src/Controller/Admin/ProjectCrudController.php`

**Rules**:
- Only System Admins can **delete** projects
- Supervisors can **view** but cannot create/edit (unless they're also project managers)
- Auditors can **view** only (no create/edit)
- Project Managers can create/edit (already filtered to their own projects)

```php
public function configureActions(Actions $actions): Actions
{
    // ... existing actions ...

    // Only admins can delete
    $actions->setPermission(Action::DELETE, 'ROLE_SYSTEM_ADMIN');

    // Supervisors can view but not edit
    if ($this->isGranted('ROLE_SUPERVISOR') && !$this->isGranted('ROLE_PROJECT_MANAGER')) {
        $actions->disable(Action::NEW, Action::EDIT);
    }

    // Auditors can only view
    if ($this->isGranted('ROLE_AUDITOR') &&
        !$this->isGranted('ROLE_PROJECT_MANAGER') &&
        !$this->isGranted('ROLE_SUPERVISOR')) {
        $actions->disable(Action::NEW, Action::EDIT);
    }

    return $actions;
}
```

## Testing

### Manual Testing Steps

1. **Test Project Manager Access**:
   - Login as: `pm_zhang` / `111`
   - Go to `/admin` → 项目基础信息
   - Verify: Only see projects where `registeredBy = pm_zhang`
   - Try creating a new project
   - Verify: `registeredBy`, `org`, `registrantOrganization` are auto-populated

2. **Test Supervisor Access**:
   - Login as: `supervisor1` / `111`
   - Go to `/admin` → 项目基础信息
   - Verify: See ALL projects (no filtering)
   - Verify: Can view but cannot create/edit projects

3. **Test System Admin Access**:
   - Login as: `admin` / `111`
   - Go to `/admin` → 项目基础信息
   - Verify: See ALL projects
   - Verify: Can create/edit/delete projects

4. **Test Auditor Access**:
   - Login as: `auditor1` / `111`
   - Go to `/admin` → 项目基础信息
   - Verify: See NO projects (until assignment system is implemented)

## Database Verification

Verify the filtering with SQL:

```sql
-- Check which projects pm_zhang registered
SELECT p.id, p.project_name, p.project_number, u.username as registered_by
FROM project p
LEFT JOIN "user" u ON p.registered_by_id = u.id
WHERE u.username = 'pm_zhang';

-- Verify org assignment
SELECT p.id, p.project_name, o.name as org_name, u.username as registered_by
FROM project p
JOIN org o ON p.org_id = o.id
LEFT JOIN "user" u ON p.registered_by_id = u.id
ORDER BY p.id;
```

## Implementation Summary

✅ **Completed**:
- Query filtering for Project Managers (only see own projects)
- Query filtering for Supervisors/Admins (see all projects)
- Auto-population of registeredBy, org, registrantOrganization on project creation
- Action permissions (delete, create, edit) based on roles
- Updated UserFixtures: all passwords changed to `111`

⚠️ **Pending** (documented separately):
- Auditor assignment system
- Security Voters for fine-grained access control
- Dashboard filtering
- Field visibility based on roles

## Test Credentials

All users now have password: **111**

- **Admin**: `admin` / `111`
- **Tech Admin**: `tech_admin` / `111`
- **Supervisor**: `supervisor1`, `supervisor2`, `supervisor3` / `111`
- **Auditor**: `auditor1` through `auditor6` / `111`
- **Project Manager**: `pm_zhang`, `pm_li`, `pm_wang`, `pm_zhao`, `pm_liu`, `pm_chen`, `pm_sun`, `pm_zhou`, `pm_zheng`, `pm_wu` / `111`

## Verification Checklist

- [x] Project managers can only see their own projects
- [x] Project managers can create new projects
- [x] New projects auto-populate registeredBy field
- [x] Supervisors can see all projects
- [x] Supervisors cannot create/edit projects (unless also PM)
- [x] System admins can see all projects
- [x] System admins can create/edit/delete projects
- [x] Auditors see no projects (until assignment system)
- [x] All user passwords updated to '111'
- [x] Fixtures reload successfully

## Next Steps

1. Implement audit assignment system for auditors
2. Create security voters for fine-grained permissions
3. Add role-specific operation buttons (继续登记, 审核项目, etc.)
4. Implement field visibility control
5. Filter dashboard statistics by role
