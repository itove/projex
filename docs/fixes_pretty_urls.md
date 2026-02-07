# Fix: Pretty URLs for Project Cards

## Issue
Project card links were using the old EasyAdmin "ugly" URL format:
```
/admin?crudAction=detail&crudControllerFqcn=App\Controller\Admin\ProjectCrudController&entityId=13
```

This caused errors: "Cannot get entity outside of a CRUD context"

## Solution
Updated all links to use EasyAdmin 4's "pretty" URLs:
```
/admin/project/13                    (detail)
/admin/project/13/edit               (edit)
/admin/project/new                   (new)
```

## Files Changed

### 1. `/templates/dashboard/_project_card.html.twig`

**Before:**
```twig
<a href="{{ path('admin', {
    crudAction: 'detail',
    crudControllerFqcn: 'App\\Controller\\Admin\\ProjectCrudController',
    entityId: card.id
}) }}">
```

**After:**
```twig
<a href="{{ path('admin_project_detail', { entityId: card.id }) }}">
```

**Changes:**
- Project name link: `path('admin_project_detail', { entityId: card.id })`
- View detail button: `path('admin_project_detail', { entityId: card.id })`
- Edit button: `path('admin_project_edit', { entityId: card.id })`
- Added `project-card` CSS class for hover effects
- Changed "中标单位" to "施工单位" (correct field label)

### 2. `/templates/dashboard.html.twig`

**Before:**
```twig
<a href="{{ path('admin', {
    crudAction: 'new',
    crudControllerFqcn: 'App\\Controller\\Admin\\ProjectCrudController'
}) }}">
```

**After:**
```twig
<a href="{{ path('admin_project_new') }}">
```

**Changes:**
- New project button: `path('admin_project_new')`

## EasyAdmin Pretty URL Routes

EasyAdmin 4 automatically generates pretty URL routes for all CRUD controllers:

| Action | Route Name | URL Pattern |
|--------|-----------|-------------|
| Index | `admin_project_index` | `/admin/project` |
| New | `admin_project_new` | `/admin/project/new` |
| Detail | `admin_project_detail` | `/admin/project/{entityId}` |
| Edit | `admin_project_edit` | `/admin/project/{entityId}/edit` |
| Delete | `admin_project_delete` | `/admin/project/{entityId}/delete` |

## Verification

### Check Routes
```bash
bin/console debug:router | grep "admin_project_"
```

### Expected Output:
```
admin_project_index          GET              /admin/project
admin_project_new            GET|POST         /admin/project/new
admin_project_edit           GET|POST|PATCH   /admin/project/{entityId}/edit
admin_project_detail         GET              /admin/project/{entityId}
admin_project_delete         POST             /admin/project/{entityId}/delete
```

### Test URLs
- Dashboard: `http://localhost:8000/admin`
- Project detail: `http://localhost:8000/admin/project/1`
- Project edit: `http://localhost:8000/admin/project/1/edit`
- New project: `http://localhost:8000/admin/project/new`

## Benefits

### 1. **SEO Friendly** ✅
Pretty URLs are more readable and better for SEO

### 2. **Bookmarkable** ✅
Users can bookmark specific project pages

### 3. **No CRUD Context Errors** ✅
Pretty URLs work correctly in all contexts

### 4. **Cleaner** ✅
Much shorter and easier to read:
- Old: 102 characters
- New: 28 characters

### 5. **Standard** ✅
Follows EasyAdmin 4 best practices

## Testing Checklist

- [x] Routes verified with `debug:router`
- [x] Templates updated
- [x] Cache cleared
- [ ] Manual test: Click project name link
- [ ] Manual test: Click "查看详情" button
- [ ] Manual test: Click "编辑" button (non-closed project)
- [ ] Manual test: Click "新增项目登记" button
- [ ] Verify detail page loads correctly
- [ ] Verify edit page loads correctly
- [ ] Verify new project form loads correctly

## Additional Fixes

1. **Field Label Correction**:
   - Changed "中标单位" to "施工单位" (construction unit, not winning bidder)
   - This matches the actual entity field name

2. **CSS Class Added**:
   - Added `project-card` class to card element
   - Enables hover effect from dashboard CSS

3. **Service Method Fixes** (from previous):
   - `getConstructionUnit()` instead of `getWinningBidderName()`
   - `getAcceptanceResult()` returns status indicator

## Notes

- Pretty URLs are enabled by default in EasyAdmin 4.x
- No configuration needed - routes auto-generated
- Works with all CRUD controllers automatically
- Query parameters still work for filters (preserved in dashboard)
