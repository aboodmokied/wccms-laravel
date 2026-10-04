# Tasks: Reporting & Dashboards (Phase 9)

**Feature**: Reporting & Dashboards (Phase 9) | **Branch**: `009-reports-dashboards` | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/009-reports-dashboards/spec.md)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Route registrations and basic file structure initialization

- [x] T001 Register Dashboard (`/dashboard`) and Reports routes (`/reports/history`, `/reports/history/{patient}/export`) in `routes/web.php`
- [x] T002 [P] Create empty service class file in `app/Services/ReportingService.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core data aggregation layer that MUST be complete before user stories can execute

**CRITICAL**: No user story work can begin until this phase is complete

- [x] T003 Create `app/Services/ReportingService.php` implementing set-based SQL aggregation queries for active patients, average treatment duration, outcome distribution percentages, and missed appointment rates.
- [x] T004 [P] Create `app/Http/Controllers/DashboardController.php` to consume `ReportingService`.
- [x] T005 [P] Create `app/Http/Controllers/ReportController.php` to handle patient history search and CSV download actions.

**Checkpoint**: Foundation ready - Controllers and ReportingService ready for User Stories.

---

## Phase 3: User Story 1 - Real-Time Executive Dashboard (Priority: P1) 🏆 MVP

**Goal**: Display live aggregated clinic KPIs and recent activity logs on `/dashboard`.

**Independent Test**: Navigate to `/dashboard`, observe live card metrics computed dynamically from DB records, and verify zero-division safety.

### Tests for User Story 1

- [x] T006 [P] [US1] Create automated feature test suite in `tests/Feature/ReportsAndDashboardTest.php` to test KPI calculations and empty-state safety.

### Implementation for User Story 1

- [x] T007 [US1] Implement `DashboardController@index` in `app/Http/Controllers/DashboardController.php` retrieving live aggregated KPIs and recent activities from `ReportingService`.
- [x] T008 [US1] Update Blade template `resources/views/dashboard.blade.php` to replace static placeholders with dynamic KPI cards, outcome progress bars, and recent activity logs.

**Checkpoint**: User Story 1 fully functional and testable independently.

---

## Phase 4: User Story 2 - Comprehensive Patient Clinical History Report & CSV Export (Priority: P2)

**Goal**: Search patient clinical history by ID/MRN and download UTF-8 BOM CSV exports.

**Independent Test**: Search for a patient on `/reports/history`, observe chronological clinical timeline, and download valid CSV file.

### Tests for User Story 2

- [x] T009 [P] [US2] Add feature tests for patient history search and CSV export streamed download in `tests/Feature/ReportsAndDashboardTest.php`.

### Implementation for User Story 2

- [x] T010 [US2] Implement `ReportController@history` in `app/Http/Controllers/ReportController.php` to search patient records and build chronological clinical timeline.
- [x] T011 [US2] Implement `ReportController@exportCsv` in `app/Http/Controllers/ReportController.php` using streamed UTF-8 BOM response for patient demographics and evaluation logs.
- [x] T012 [US2] Update Blade template `resources/views/reports/history.blade.php` with search form, patient profile summary card, clinical timeline, and CSV export action button.

**Checkpoint**: User Stories 1 AND 2 functional independently.

---

## Phase 5: User Story 3 - Interactive Date Range & Analytical Filtering (Priority: P3)

**Goal**: Filter aggregate dashboard metrics and patient history reports by quick date range presets (All Time, Last 30 Days, YTD).

**Independent Test**: Select a quick date filter (e.g. `?period=30days`) and verify KPI numbers recalculate for that period.

### Tests for User Story 3

- [x] T013 [P] [US3] Add feature tests in `tests/Feature/ReportsAndDashboardTest.php` for period query parameters (`?period=30days`, `?period=ytd`).

### Implementation for User Story 3

- [x] T014 [US3] Add quick period filter buttons (كافة الأوقات / آخر 30 يومًا / السنة الحالية) in `resources/views/dashboard.blade.php` and `resources/views/reports/history.blade.php`.

**Checkpoint**: All user stories functional and integrated cleanly.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Formatting, testing verification, and browser visual validation

- [x] T015 Run PHPUnit feature test suite: `php artisan test --filter=ReportsAndDashboardTest`
- [x] T016 Run Pint code formatter: `vendor/bin/pint --dirty --format agent`
- [x] T017 Run browser verification script to validate UI, RTL layout, and CSV export download.

---

## Dependencies & Execution Order

### Phase Dependencies
- **Setup (Phase 1)**: Can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS User Stories.
- **User Story 1 (Phase 3)**: Depends on Phase 2 - MVP.
- **User Story 2 (Phase 4)**: Depends on Phase 2.
- **User Story 3 (Phase 5)**: Depends on Phase 3 and Phase 4.
- **Polish (Phase 6)**: Depends on completion of User Stories.

---

## Implementation Strategy

### MVP First (User Story 1 Only)
1. Complete Phase 1 & Phase 2.
2. Complete Phase 3 (User Story 1).
3. Validate `/dashboard` live metrics via PHPUnit.

### Incremental Delivery
1. Deliver US1 (Executive Dashboard KPIs).
2. Deliver US2 (Patient History Search & CSV Export).
3. Deliver US3 (Date Period Quick Filters).
