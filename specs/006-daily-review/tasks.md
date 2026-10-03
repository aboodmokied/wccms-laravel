# Tasks: Phase 6 — Daily Review

**Input**: Design documents from `/specs/006-daily-review/`  
**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/daily-review-api.md](contracts/daily-review-api.md)

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3, US4)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project routing and basic registration endpoint setup

- [x] T001 Register daily review routes (`GET /registrations/{registration}/daily-reviews` and `POST /registrations/{registration}/daily-reviews`) in `routes/web.php` protected by `auth` middleware.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core DB-First Eloquent models and form request validator required by all user stories

**⚠️ CRITICAL**: Must complete before user stories can begin

- [x] T002 Ensure DB-First Eloquent models `app/Models/WcDailyReview.php` and `app/Models/WcDailyReviewDetail.php` are configured with `$primaryKey`, `$keyType = 'string'`, `$incrementing = false`, `$timestamps = false`, fillable arrays, and relationship methods per [data-model.md](data-model.md).
- [x] T003 [P] Add Eloquent relationship methods `dailyReviews()` on `app/Models/WcRegistration.php` and `dailyReviewDetails()` / `getLatestDressingAttribute()` / `getLatestMedicationAttribute()` on `app/Models/WcRegDiagnosis.php`.
- [x] T004 Create form request validator `app/Http/Requests/StoreDailyReviewRequest.php` enforcing validation rules for `nurse_code`, `overall_status` (`IMPROVED_DISCHARGE`, `IMPROVED_SCHEDULED`, `WORSENED_STAY`, `DEATH`), and per-wound detail arrays per [contracts/daily-review-api.md](contracts/daily-review-api.md). *(gap-filled: file was missing, created and verified)*

**Checkpoint**: Foundation ready - user story implementation can now begin.

---

## Phase 3: User Story 1 - Daily Review History & Wound Overview (Priority: P1) 🎯 MVP

**Goal**: Render daily review workspace displaying read-only patient summary card, active wound diagnosis tabs (deriving latest dressing/medication from `wc_daily_review_detail_tb`), and chronological daily review history timeline.

**Independent Test**: Navigate to `/registrations/{registration}/daily-reviews` for an active registration. Verify patient summary, active wound tabs, and daily review history timeline render accurately.

### Implementation for User Story 1

- [x] T005 [P] [US1] Create patient summary partial in `resources/views/reviews/partials/patient_summary.blade.php` rendering patient demographics, department, and registration status.
- [x] T006 [P] [US1] Create wound diagnosis tabs partial in `resources/views/reviews/partials/wound_tabs.blade.php` rendering active wound diagnosis, type, current dressing, and medication derived from latest `wc_daily_review_detail_tb`. *(gap-filled: file was missing, created and verified)*
- [x] T007 [P] [US1] Create daily review history timeline partial in `resources/views/reviews/partials/review_timeline.blade.php` rendering past visit cards with review date, nurse name, notes, overall status badge, and per-wound outcomes.
- [x] T008 [US1] Create `app/Http/Controllers/DailyReviewController.php` with `index` method loading registration with relations and rendering main view `resources/views/reviews/daily.blade.php`.

**Checkpoint**: At this point, User Story 1 is fully functional and testable independently.

---

## Phase 4: User Story 2 - Multi-Wound Daily Follow-Up Entry (Priority: P1) 🎯 MVP

**Goal**: Record today's follow-up visit with observations, updated dressings, updated medications, and outcome assessments per wound and overall visit outcome.

**Independent Test**: Open "+ Add Daily Review" modal, submit observations and dressings for active wounds, select overall outcome, and save. Verify `WC_DAILY_REVIEW_TB` and `WC_DREVD_DETAIL_TB` records are created in DB.

### Implementation for User Story 2

- [x] T009 [P] [US2] Create multi-wound daily review modal partial in `resources/views/reviews/partials/daily_review_modal.blade.php` with separated overall visit outcome section and per-wound detail evaluation sections.
- [x] T010 [US2] Implement `store` method in `app/Http/Controllers/DailyReviewController.php` using `DB::transaction()` to insert header (`WcDailyReview`), detail rows (`WcDailyReviewDetail`), and dual audit fields (`WC_DREV_NURSE_CD` vs `WC_DREV_ENTRY_USER_CD`).

**Checkpoint**: At this point, User Stories 1 AND 2 are fully functional independently.

---

## Phase 5: User Story 3 - Automated Follow-Up Appointment Scheduling on Discharge (Priority: P2)

**Goal**: Automatically transition registration status to `DISCHARGED` and generate a pending scheduled review appointment when overall outcome `IMPROVED_SCHEDULED` or `IMPROVED_DISCHARGE` is selected.

**Independent Test**: Save a daily review with overall outcome `IMPROVED_SCHEDULED`. Verify registration status becomes `DISCHARGED` and `WC_SCHEDULED_REVIEW_TB` record is created.

### Implementation for User Story 3

- [x] T011 [US3] Update `DailyReviewController@store` transaction logic to auto-transition `WC_REGISTRATION_TB` status to `DISCHARGED` with `WC_REG_DISCHARGED_AT = now()` and `WC_REG_DISCHARGED_BY_EMP_CD` when outcome is `IMPROVED_DISCHARGE` or `IMPROVED_SCHEDULED`.
- [x] T012 [US3] Update `DailyReviewController@store` transaction logic to create pending `WC_SCHEDULED_REVIEW_TB` record and link `WC_DREV_SCHEDULED_REVIEW_CD` when outcome is `IMPROVED_SCHEDULED`.

**Checkpoint**: User Story 3 automated discharge and appointment scheduling active.

---

## Phase 6: User Story 4 - Discharged Registration Review Lock (Priority: P3)

**Goal**: Disable creating new daily reviews on `DISCHARGED` registrations while maintaining read-only historical access.

**Independent Test**: View `/registrations/{registration}/daily-reviews` for a discharged registration. Verify "+ Add Daily Review" button is hidden/disabled and status banner is rendered.

### Implementation for User Story 4

- [x] T013 [US4] Update `resources/views/reviews/daily.blade.php` to render status alert banner and hide/disable "+ Add Daily Review" button when registration status is `DISCHARGED`.
- [x] T014 [US4] Add validation guard in `DailyReviewController@store` returning 403 HTTP response if attempting to add a daily review on a `DISCHARGED` registration.

**Checkpoint**: Discharged registration lock active.

---

## Phase 7: Feature Tests & Verification

- [x] T015 [P] Create feature test `tests/Feature/DailyReviewTest.php` covering workspace viewing, multi-wound review creation, dual audit tracking, automatic discharge, and lock behavior. *(gap-filled: replaced model-only test with full HTTP-level feature test — 6 tests, 22 assertions, all passing)*

---

## Phase 8: Polish & Cross-Cutting Concerns

- [x] T016 Run PHP code formatter `vendor/bin/pint --dirty --format agent`.
- [x] T017 Verify end-to-end functionality using [quickstart.md](quickstart.md) manual verification steps.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories.
- **User Stories (Phase 3+)**: All depend on Foundational phase completion.
  - US1 & US2 (P1) → US3 (P2) → US4 (P3).
- **Polish (Final Phase)**: Depends on user stories completion.

### Parallel Opportunities

- T003, T005, T006, T007, T009, T015 can run in parallel as they touch independent files.
