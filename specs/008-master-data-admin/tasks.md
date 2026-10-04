# Tasks: Phase 8 — Master Data Administration

**Feature Branch**: `008-master-data-admin`  
**Input**: Design artifacts from `specs/008-master-data-admin/` (`spec.md`, `plan.md`, `data-model.md`, `contracts/master-data-api.md`, `quickstart.md`, `research.md`)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verify DB-First Eloquent master data model definitions and attributes across all 7 reference domain tables.

- [X] T001 Verify DB-First Eloquent model definitions for reference entities in `app/Models/WcDepartment.php`, `app/Models/WcPmh.php`, `app/Models/WcDiagnosis.php`, `app/Models/WcWoundType.php`, `app/Models/WcDressing.php`, `app/Models/WcMedication.php`, `app/Models/WcProcedure.php` (`$table = '*_tb'`, `$primaryKey = '*_CODE'`, `$incrementing = false`, `$keyType = 'string'`, `$timestamps = false`).

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Master data Web routes, shared Blade partials, and navigation structure.

- [X] T002 [P] Register Admin master data Web routes in `routes/web.php` for all 7 domain resource endpoints (`/admin/departments`, `/admin/pmh`, `/admin/diagnoses`, `/admin/wound-types`, `/admin/dressings`, `/admin/medications`, `/admin/procedures`) wrapped in `middleware(['hospital.jwt', 'role:ADMIN'])`.
- [X] T003 [P] Create shared Admin domain navigation partial `resources/views/admin/master_data/partials/domain-nav.blade.php` with tab links for all 7 master data domains and active tab highlights.
- [X] T004 [P] Create shared item form modal partial `resources/views/admin/master_data/partials/item-modal.blade.php` supporting create and edit actions.
- [X] T005 [P] Create shared item table partial `resources/views/admin/master_data/partials/item-table.blade.php` rendering item code, name, status badge (`IS_ACTIVE`), edit modal trigger, and toggle active form.

---

## Phase 3: User Story 1 - Admin Master Data Workspace & RESTful Resource Endpoints (Priority: P1) 🎯 MVP

**Goal**: Provide 7 dedicated RESTful resource controllers and views for managing master data tables with Arabic RTL layout, status badges, and search filtering.

**Independent Test**: Log in as an Admin user, navigate to `/admin/departments`, `/admin/medications`, or any domain route, and verify that items are correctly listed with active/inactive status badges, search filtering, and pagination in `tests/Feature/MasterDataAdminTest.php`.

### Implementation for User Story 1

- [X] T006 [P] [US1] Create feature tests for master data index listing, search filtering, and active/inactive status display in `tests/Feature/MasterDataAdminTest.php`.
- [X] T007 [P] [US1] Create `DepartmentController` in `app/Http/Controllers/Admin/DepartmentController.php` implementing `index`, `store`, `update`, and `toggleActive`.
- [X] T008 [P] [US1] Create `PmhController` in `app/Http/Controllers/Admin/PmhController.php` implementing `index`, `store`, `update`, and `toggleActive`.
- [X] T009 [P] [US1] Create `DiagnosisController` in `app/Http/Controllers/Admin/DiagnosisController.php` implementing `index`, `store`, `update`, and `toggleActive`.
- [X] T010 [P] [US1] Create `WoundTypeController` in `app/Http/Controllers/Admin/WoundTypeController.php` implementing `index`, `store`, `update`, and `toggleActive`.
- [X] T011 [P] [US1] Create `DressingController` in `app/Http/Controllers/Admin/DressingController.php` implementing `index`, `store`, `update`, and `toggleActive`.
- [X] T012 [P] [US1] Create `MedicationController` in `app/Http/Controllers/Admin/MedicationController.php` implementing `index`, `store`, `update`, and `toggleActive`.
- [X] T013 [P] [US1] Create `ProcedureController` in `app/Http/Controllers/Admin/ProcedureController.php` implementing `index`, `store`, `update`, and `toggleActive`.
- [X] T014 [US1] Create master data index view in `resources/views/admin/master_data/index.blade.php` integrating domain nav tabs, search input, stats, and item table.

---

## Phase 4: User Story 2 - Creating & Editing Reference Items (Priority: P1)

**Goal**: Allow Administrators to create new reference entries with UUID primary keys or update existing entry names with unique validation within the domain.

**Independent Test**: Add a new item (e.g. new medication), edit its name, and assert persistence and unique name validation error handling in `tests/Feature/MasterDataAdminTest.php`.

### Implementation for User Story 2

- [X] T015 [P] [US2] Add feature tests for item creation with UUID primary key, name update, and duplicate name validation error handling in `tests/Feature/MasterDataAdminTest.php`.
- [X] T016 [US2] Implement store and update validation logic across all 7 domain controllers in `app/Http/Controllers/Admin/` validating `name` (`required|string|max:255|unique:{table},{PREFIX}_NAME`).

---

## Phase 5: User Story 3 - Activation & Deactivation Toggle (No Hard Deletes) (Priority: P1)

**Goal**: Support toggling active status (`IS_ACTIVE` 1 ↔ 0) while completely prohibiting hard deletes (`DELETE`), ensuring deactivated items are hidden from active form dropdowns but preserved in historical logs.

**Independent Test**: Deactivate an item, verify `IS_ACTIVE = 0`, verify it is excluded from active form select queries (`where('IS_ACTIVE', 1)`), and verify historical records display its name in `tests/Feature/MasterDataAdminTest.php`.

### Implementation for User Story 3

- [X] T017 [P] [US3] Add feature tests for toggling `IS_ACTIVE`, verifying no hard delete, and verifying form dropdown filtering in `tests/Feature/MasterDataAdminTest.php`.
- [X] T018 [US3] Audit and update form dropdown queries in `app/Http/Controllers/RegistrationRequestController.php`, `app/Http/Controllers/RegistrationController.php`, and `app/Http/Controllers/DailyReviewController.php` to fetch only active items (`where('IS_ACTIVE', 1)`).

---

## Phase 6: User Story 4 - Strict Role-Based Access Control (RBAC) (Priority: P1)

**Goal**: Restrict all master data administration routes and actions exclusively to users with `PER_USER_ROLE === 'ADMIN'`.

**Independent Test**: Attempt to access `/admin/departments`, `/admin/medications`, or any master data POST/PUT/PATCH endpoint as a `NURSE` or unassigned user and assert HTTP 403 Forbidden in `tests/Feature/MasterDataAdminTest.php`.

### Implementation for User Story 4

- [X] T019 [P] [US4] Add feature tests for RBAC enforcement asserting non-admin users receive HTTP 403 Forbidden in `tests/Feature/MasterDataAdminTest.php`.
- [X] T020 [US4] Update Admin dashboard index in `resources/views/admin/index.blade.php` to display quick links to Master Data administration domains.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Full verification, code style formatting, and scenario validation.

- [X] T021 [P] Execute full test suite `php artisan test --filter=MasterDataAdminTest` and verify 100% pass rate.
- [X] T022 [P] Format codebase using Pint formatter: `vendor/bin/pint --dirty --format agent`.
- [X] T023 Run validation scenarios from `specs/008-master-data-admin/quickstart.md`.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately.
- **Foundational (Phase 2)**: Depends on Setup (Phase 1) - BLOCKS user stories.
- **User Story 1 (Phase 3 - P1)**: Depends on Foundational (Phase 2).
- **User Story 2 (Phase 4 - P1)**: Depends on User Story 1 (Phase 3).
- **User Story 3 (Phase 5 - P1)**: Depends on User Story 1 (Phase 3).
- **User Story 4 (Phase 6 - P1)**: Depends on Foundational (Phase 2).
- **Polish (Phase 7)**: Depends on completion of user stories.

### Parallel Opportunities

- Domain controllers `T007` to `T013` can be created in parallel.
- Test tasks marked `[P]` (`T006`, `T015`, `T017`, `T019`) can run in parallel within their respective story phases.
- Shared Blade partials `T003`, `T004`, `T005` [P] can be created in parallel.
