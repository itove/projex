# Project Lifecycle Timeline Implementation

## Overview

Implemented a comprehensive project lifecycle visualization on the project detail page, showing all 7 stages with visual indicators, progress tracking, and quick action links.

## Features Implemented

### 1. **Project Summary Header** (4 Key Metrics)
- **Overall Progress**: Visual percentage with progress bar
- **Completed Stages**: X/7 counter
- **Current Stage**: Name with optional progress badge
- **Total Files**: Aggregate file count across all stages

### 2. **Horizontal Timeline** (Visual Progress Track)
- 7 stages displayed horizontally with connecting lines
- Color-coded status indicators:
  - ✅ **Green (Completed)**: Check circle icon
  - ⚠️ **Yellow (In Progress)**: Spinning icon with progress %
  - ○ **Gray (Not Started)**: Empty circle
- Connecting lines show progression
- Responsive design (adapts to mobile screens)

### 3. **Stage Detail Cards** (7 Cards, 2 columns)

Each card displays:

#### **Completed Stages**
- Green border and header
- Start/completion dates
- Key organization/unit information
- File and image counts
- Actions:
  - 🔍 **查看详情** (View Details)
  - 📁 **查看文档** (View Documents)

#### **In-Progress Stages**
- Yellow border and header
- Start date
- Progress bar (for implementation stage)
- Current information
- File counts
- Actions:
  - 🔍 **查看详情** (View Details)
  - ✏️ **更新进度** (Update Progress)
  - 📁 **查看文档** (View Documents)

#### **Not Started Stages**
- Gray border and header
- Prerequisites message
- Collapsible requirements section
- Actions:
  - ➕ **开始登记** (Start Registration) - if prerequisites met
  - 🔒 **前置条件未满足** (Prerequisites Not Met) - disabled button
  - ❓ **查看要求** (View Requirements) - collapsible info

## Files Created

### Service Layer

#### `/src/Service/ProjectDisplayService.php` (ENHANCED)

**New Methods:**
- `getLifecycleStages(Project $project)`: array
  - Returns all 7 stages with status, info, and route names
- `getStageStatus(object $stageEntity)`: string
  - Determines if stage is completed, in_progress, or not_started
- `getStageInfo(object $stageEntity, string $type)`: ?string
  - Extracts key information from stage entity
- `getStageFileCount(object $stageEntity)`: int
  - Counts files attached to a stage
- `getProjectSummary(Project $project)`: array
  - Calculates overall metrics for summary header

### Controller Layer

#### `/src/Controller/Admin/ProjectCrudController.php` (ENHANCED)

**Changes:**
- Added `ProjectDisplayService` dependency injection
- Added `detail(AdminContext $context)` method
  - Fetches lifecycle stages and summary
  - Passes data to custom detail template

### Template Layer

#### `/templates/admin/project/detail.html.twig` (NEW)
Main detail page template that extends EasyAdmin's default detail page

**Features:**
- Custom title with status badge
- Includes lifecycle section at top
- Standard EasyAdmin detail content below
- Bootstrap tooltip initialization

#### `/templates/admin/project/_lifecycle_section.html.twig` (NEW)
Complete lifecycle section with summary, timeline, and cards

**Structure:**
1. Summary header card (4 metrics)
2. Timeline card (horizontal progress track)
3. Stage detail cards grid (2 columns)

#### `/templates/admin/project/_lifecycle_timeline.html.twig` (NEW)
Horizontal timeline component

**Features:**
- Responsive flex layout
- Color-coded icons and connecting lines
- Progress badges
- Embedded CSS for visual styling

#### `/templates/admin/project/_stage_card.html.twig` (NEW)
Individual stage card component

**Features:**
- Dynamic header color based on status
- Conditional content based on stage existence
- Progress bar for implementation stage
- File/image counts
- Context-aware action buttons
- Collapsible requirements section
- Hover effects

## Data Flow

```
User visits project detail page
    ↓
ProjectCrudController::detail()
    ↓
ProjectDisplayService::getLifecycleStages()
    ├── Checks each stage entity exists
    ├── Determines status (completed/in_progress/not_started)
    ├── Extracts key information
    └── Counts files
    ↓
ProjectDisplayService::getProjectSummary()
    ├── Calculates overall progress
    ├── Counts completed stages
    ├── Gets current stage
    └── Aggregates total files
    ↓
Renders templates/admin/project/detail.html.twig
    ├── _lifecycle_section.html.twig
    │   ├── Summary header
    │   ├── _lifecycle_timeline.html.twig
    │   └── _stage_card.html.twig (× 7)
    └── Standard EasyAdmin detail content
```

## Stage Configuration

| # | Name | Icon | Entity | Route Prefix |
|---|------|------|--------|--------------|
| 1 | 前期决策流程 | fa-file-alt | PreliminaryDecision | admin_preliminary_decision |
| 2 | 立项流程 | fa-check-square | ProjectApproval | admin_project_approval |
| 3 | 规划设计流程 | fa-pencil-ruler | PlanningDesign | admin_planning_design |
| 4 | 施工准备流程 | fa-tools | ConstructionPreparation | admin_construction_preparation |
| 5 | 施工实施流程 | fa-hard-hat | ConstructionImplementation | admin_construction_implementation |
| 6 | 竣工验收流程 | fa-clipboard-check | CompletionAcceptance | admin_completion_acceptance |
| 7 | 竣工结算流程 | fa-calculator | SettlementAccounts | admin_settlement_accounts |

## Action Links

### Link Generation Examples

**View Detail:**
```twig
{{ path('admin_preliminary_decision_detail', {entityId: stage.entity.id}) }}
```

**Edit/Update:**
```twig
{{ path('admin_project_approval_edit', {entityId: stage.entity.id}) }}
```

**View Documents:**
```twig
{{ path('admin_file_index') }}?filters[preliminary_decision]={{ stage.entity.id }}
```

**Start Registration:**
```twig
{{ path('admin_planning_design_new') }}?project={{ project.id }}
```

## Status Logic

### Completed
A stage is considered **completed** if:
- Entity exists AND
- Has `completionDate` set (for most stages)
- OR has `acceptanceDate` set (for acceptance stage)

### In Progress
A stage is **in progress** if:
- Entity exists AND
- Does NOT have completion date

### Not Started
A stage is **not started** if:
- Entity does NOT exist (null)

## Prerequisites Logic

A stage can be started if:
1. It's stage #1 (no prerequisites), OR
2. Previous stage status is "completed"

Otherwise, the "开始登记" button is disabled with message "前置条件未满足"

## Visual Design

### Colors
- **Success (Green)**: #198754 - Completed stages
- **Warning (Yellow)**: #ffc107 - In-progress stages
- **Secondary (Gray)**: #6c757d - Not started stages
- **Primary (Blue)**: #0d6efd - Overall progress
- **Info (Cyan)**: #0dcaf0 - File counts

### Responsive Breakpoints
- **Desktop (≥1200px)**: 2 columns for stage cards
- **Tablet (768px-1199px)**: 2 columns
- **Mobile (<768px)**: 1 column, smaller icons/text

### Animations
- Card hover effect: lift up 3px
- Progress spinner: rotating animation
- Smooth transitions on all hover states

## Testing Checklist

### Visual Tests
- [ ] Summary header displays 4 metrics correctly
- [ ] Timeline shows all 7 stages horizontally
- [ ] Completed stages have green checkmarks
- [ ] In-progress stages show yellow spinner
- [ ] Not-started stages show gray circles
- [ ] Stage cards display in 2 columns (desktop)
- [ ] Cards have correct color borders

### Functional Tests
- [ ] Click project name → goes to detail page
- [ ] "查看详情" button → opens stage detail
- [ ] "更新进度" button → opens edit form
- [ ] "查看文档" button → filters file list
- [ ] "开始登记" button → opens new form (with project pre-filled)
- [ ] Prerequisites button disabled when needed
- [ ] "查看要求" expands requirements section

### Data Tests
- [ ] File counts match actual files
- [ ] Dates display correctly
- [ ] Progress percentages accurate
- [ ] Status badges show correct state
- [ ] Organization/unit names display

### Responsive Tests
- [ ] Timeline adapts to mobile screens
- [ ] Stage cards stack on mobile
- [ ] Text remains readable on small screens
- [ ] Icons scale appropriately

## Future Enhancements

### 1. **Document Upload from Stage Card**
Add upload button directly on stage cards

### 2. **Stage Completion Confirmation**
Modal dialog to confirm stage completion with checklist

### 3. **Timeline Interaction**
Click timeline stage to scroll to corresponding card

### 4. **Progress Animation**
Animated progress bars on page load

### 5. **Stage Notifications**
Show notification count for stages requiring attention

### 6. **Export Timeline**
Download timeline as PDF/image

### 7. **History Timeline**
Show historical timeline of stage completions

## Notes

- All templates use Bootstrap 5 classes
- Font Awesome 6 icons throughout
- Fully compatible with EasyAdmin 4.x
- No JavaScript dependencies (except Bootstrap)
- Accessible (ARIA attributes on progress bars)
- Translatable (all text in Chinese per requirements)

## Example URL

```
http://localhost:8000/admin/project/1
```

This will display the full project detail page with lifecycle timeline at the top!
