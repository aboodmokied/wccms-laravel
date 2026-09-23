# Implementation Tasks: Phase 3 — Auth & RBAC

**Feature Branch**: `003-auth-rbac` | **Date**: 2026-09-22 | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/003-auth-rbac/spec.md) | **Plan**: [plan.md](file:///e:/wamp64/www/wccms/specs/003-auth-rbac/plan.md)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Environment configuration and core authentication dependencies

- [x] T001 Configure `.env.example` and `config/services.php` with `HOSPITAL_JWT_SECRET` and `APP_HOSPITAL_LOGIN_URL` configuration keys in `config/services.php`
- [x] T002 [P] Verify and install JWT handling dependency (`firebase/php-jwt`) in `composer.json`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core DB-First Eloquent models and relationship mappings required before any user story work

- [x] T003 [P] Implement DB-First Eloquent `User` model in `app/Models/User.php` mapping to `per_users_tb` (`$table = 'per_users_tb'`, `$primaryKey = 'PER_USER_CODE'`, `$keyType = 'string'`, `$incrementing = false`, `$timestamps = false`, `$casts = ['PER_USER_IS_ACTIVE' => 'boolean']`, fillable excluding `PER_USER_PASSWORD`)
- [x] T004 [P] Implement DB-First Eloquent `Employee` model in `app/Models/Employee.php` mapping to `per_employee_tb` (`$table = 'per_employee_tb'`, `$primaryKey = 'PER_EMP_CODE'`, `$keyType = 'string'`, `$incrementing = false`, `$timestamps = false`) and define `user()` relationship (`belongsTo(User::class, 'PER_EMP_USER_CD', 'PER_USER_CODE')`)
- [x] T005 Define `employee()` `hasOne` relationship (`hasOne(Employee::class, 'PER_EMP_USER_CD', 'PER_USER_CODE')`), `isAdmin()`, and `isNurse()` helper methods in `app/Models/User.php`
- [x] T006 Write unit test for `User` and `Employee` DB-First key definitions and relationships in `tests/Unit/UserModelTest.php`

**Checkpoint**: Models and DB-First foundation ready — user story tasks can now proceed.

---

## Phase 3: User Story 1 - Identity Resolution via Hospital-System Session (Priority: P1) 🎯 MVP

**Goal**: Resolve incoming hospital user identity from signed JWTs, auto-provision new identities in pending approval state (`PER_USER_ROLE = NULL`, `PER_USER_IS_ACTIVE = 0`), enforce active status overrides, and redirect unauthenticated requests to `APP_HOSPITAL_LOGIN_URL`.

**Independent Test**: Simulate requests with valid, missing, expired, new, and deactivated JWT tokens and verify access control and auto-provisioning behavior.

### Implementation & Tests for User Story 1

- [x] T007 [P] [US1] Create feature test `tests/Feature/HospitalJwtAuthMiddlewareTest.php` testing JWT token resolution, redirect on missing/invalid token, auto-provisioning with `ROLE=NULL` and `ACTIVE=0`, and active override enforcement
- [x] T008 [US1] Implement `HospitalJwtAuthMiddleware` in `app/Http/Middleware/HospitalJwtAuthMiddleware.php` inspecting `Authorization: Bearer` header or `hospital_token` cookie, verifying JWT signature via `HOSPITAL_JWT_SECRET`, auto-provisioning missing `PER_USERS_TB` records (`PER_USER_ROLE=NULL`, `PER_USER_IS_ACTIVE=0`), and handling redirect to `APP_HOSPITAL_LOGIN_URL` (web 302) or 401 JSON error (`"جلسة العمل غير صالحة، يرجى تسجيل الدخول عبر نظام المستشفى"`)
- [x] T009 [US1] Implement local logout controller in `app/Http/Controllers/Auth/LogoutController.php` flushing Laravel application session without calling external hospital endpoints
- [x] T010 [US1] Register `HospitalJwtAuthMiddleware` alias and apply to protected web route group in `bootstrap/app.php` and `routes/web.php`

**Checkpoint**: At this point, User Story 1 (Identity Resolution MVP) is fully functional and testable independently.

---

## Phase 4: User Story 2 - Role-Based Access Control & Action Authorization (Priority: P1)

**Goal**: Strictly enforce `ADMIN` vs `NURSE` permissions across system routes, allowing both roles to access clinical forms while restricting admin screens exclusively to `ADMIN` with Arabic 403 error messages ("غير مصرح لك بالوصول إلى هذه الصفحة").

**Independent Test**: Request `/admin/*` routes under `NURSE` identity and verify HTTP 403 response with Arabic authorization error.

### Implementation & Tests for User Story 2

- [x] T011 [P] [US2] Create feature test `tests/Feature/RbacAuthorizationTest.php` testing `NURSE` access denied to `/admin/*` with HTTP 403 Arabic message, `ADMIN` full access allowed, and `NURSE`/`ADMIN` shared clinical access allowed
- [x] T012 [US2] Implement RBAC middleware `EnsureUserHasRole` in `app/Http/Middleware/EnsureUserHasRole.php` checking `PER_USER_ROLE` against required roles (`ADMIN` vs `NURSE`), rendering Arabic 403 view/JSON (`"غير مصرح لك بالوصول إلى هذه الصفحة"`) when unauthorized
- [x] T013 [US2] Register Laravel Auth Gates (`admin-only`, `clinic-staff`) in `app/Providers/AppServiceProvider.php`
- [x] T014 [US2] Apply `role:ADMIN` middleware restriction to admin route groups in `routes/web.php`

**Checkpoint**: User Story 2 is complete — RBAC authorization is strictly enforced across all routes.

---

## Phase 5: User Story 3 - Linkage of User Account to Employee Profile (Priority: P2)

**Goal**: Link `PER_USERS_TB` user accounts to `PER_EMPLOYEE_TB` employee profiles via `PER_EMP_USER_CD`, displaying the employee's full Arabic name (`PER_EMP_FULL_NAME`) in the top navigation header and preserving Actor vs. Data-Entry separation.

**Independent Test**: Authenticate as a user linked to an employee profile and verify header navbar renders employee full name; verify unlinked accounts fall back gracefully.

### Implementation & Tests for User Story 3

- [x] T015 [P] [US3] Add user-employee linkage tests in `tests/Feature/UserManagementTest.php` verifying linked employee full name is retrieved for UI header display
- [x] T016 [US3] Update top header blade view component in `resources/views/components/navbar.blade.php` to display `$user->employee?->PER_EMP_FULL_NAME` and role badge in Arabic

**Checkpoint**: User Story 3 is complete — employee profile linkage and header display are fully integrated.

---

## Phase 6: User Story 4 - Role Assignment & WCCMS Access Management (Priority: P3)

**Goal**: Provide an admin management panel for `ADMIN` users to manage user role assignments (`ADMIN`/`NURSE`), toggle WCCMS access active/inactive (`PER_USER_IS_ACTIVE`), and associate user accounts with employee profiles.

**Independent Test**: Perform role updates and active status toggles as `ADMIN`, verifying immediate enforcement on subsequent user requests.

### Implementation & Tests for User Story 4

- [x] T017 [P] [US4] Add user access management tests in `tests/Feature/UserManagementTest.php` testing role assignment, employee linkage update, and immediate session block upon `PER_USER_IS_ACTIVE = 0` toggle
- [x] T018 [US4] Create form request `UpdateUserAccessRequest` in `app/Http/Requests/UpdateUserAccessRequest.php` validating `PER_USER_ROLE` (`nullable|in:ADMIN,NURSE`), `PER_USER_IS_ACTIVE` (`boolean`), and `PER_EMP_CODE` (`nullable|string|size:36`)
- [x] T019 [US4] Implement `UserController` in `app/Http/Controllers/Admin/UserController.php` providing `index()` listing users with employee details, and `update()` handling role assignment, employee linkage via `PER_EMP_USER_CD`, and status toggling
- [x] T020 [US4] Create user management Blade view in `resources/views/admin/users/index.blade.php` displaying Arabic RTL user table with role dropdowns, employee assignment modal, and active/inactive toggle switches
- [x] T021 [US4] Register user management routes (`/admin/users`, `/admin/users/{user}`) guarded by `role:ADMIN` middleware in `routes/web.php`

**Checkpoint**: All user stories (US1 through US4) are now fully functional and testable.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Formatting enforcement, test suite verification, and quickstart scenario validation

- [x] T022 [P] Format all modified/created PHP files using `vendor/bin/pint --format agent`
- [x] T023 Run full test suite via `php artisan test --compact` to verify 100% pass rate
- [x] T024 Validate end-to-end verification scenarios in `quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user story tasks.
- **User Stories (Phases 3–6)**: Depend on Foundational phase completion. Proceed in priority order: US1 (P1) → US2 (P1) → US3 (P2) → US4 (P3).
- **Polish (Phase 7)**: Depends on completion of all user story tasks.

### Parallel Opportunities

- **Phase 1**: T002 can run in parallel with T001.
- **Phase 2**: T003 (`User`) and T004 (`Employee`) can run in parallel.
- **Phase 3 (US1)**: T007 (test scaffold) can run in parallel with T008 design.
- **Phase 4 (US2)**: T011 (test scaffold) can run in parallel with T012 design.
- **Phase 5 (US3)**: T015 (test scaffold) can run in parallel with T016 design.
- **Phase 6 (US4)**: T017 (test scaffold) can run in parallel with T018/T019 design.
- **Phase 7**: T022 (Pint formatting) can run in parallel with documentation checks.

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 (Setup) and Phase 2 (Foundational models).
2. Complete Phase 3 (User Story 1 - Identity Resolution Middleware).
3. **STOP and VALIDATE**: Run `HospitalJwtAuthMiddlewareTest` to confirm identity resolution, auto-provisioning (`ROLE=NULL`, `ACTIVE=0`), and redirect behavior.

### Incremental Delivery

1. Phase 1 + 2 → Foundational DB-First models ready.
2. Phase 3 (US1) → Identity Resolution MVP ready.
3. Phase 4 (US2) → RBAC Enforcement (`ADMIN` vs `NURSE`) ready.
4. Phase 5 (US3) → Employee Navbar Display & Linkage ready.
5. Phase 6 (US4) → Admin User Management & Role Assignment Panel ready.
6. Phase 7 → Pint formatting and full suite verification complete.
