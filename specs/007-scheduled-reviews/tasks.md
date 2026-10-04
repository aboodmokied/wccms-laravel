# Tasks: Phase 7 — Scheduled Reviews & Appointments Management

**Feature Branch**: `007-scheduled-reviews`  
**Input**: Design artifacts from `specs/007-scheduled-reviews/` (`spec.md`, `plan.md`, `data-model.md`, `contracts/scheduled-review-api.md`, `quickstart.md`)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verify core DB-First Eloquent model definitions and entity relationships.

- [X] T001 Verify DB-First Eloquent model definition in `app/Models/WcScheduledReview.php` (`$table = 'wc_scheduled_review_tb'`, `$primaryKey = 'WC_SREV_CODE'`, `$incrementing = false`, `$keyType = 'string'`, `$timestamps = false`, `$fillable = ['WC_SREV_CODE', 'WC_SREV_REGISTRATION_CD', 'WC_SREV_CREATED_BY_CD', 'WC_SREV_SCHEDULED_DATE', 'WC_SREV_VISIT_SERIAL', 'WC_SREV_STATUS', 'WC_SREV_NOTES', 'WC_SREV_CREATED_ON', 'WC_SREV_COMPLETED_ON']`).
- [X] T002 Verify model relationships in `app/Models/WcDailyReview.php` (`belongsTo(WcScheduledReview::class, 'WC_DREV_SCHEDULED_REVIEW_CD', 'WC_SREV_CODE')`) and `app/Models/WcRegistration.php` (`hasMany(WcScheduledReview::class, 'WC_SREV_REGISTRATION_CD', 'WC_REG_CODE')`).

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core routing, service infrastructure, and navigation links.

- [X] T003 [P] Register Web routes for Scheduled Reviews in `routes/web.php` (`GET /reviews/scheduled`, `POST /reviews/scheduled/{scheduledReview}/fulfill`, `PUT /reviews/scheduled/{scheduledReview}/reschedule`, `POST /reviews/scheduled/{scheduledReview}/cancel`).
- [X] T004 [P] Create base `ScheduledReviewService` class in `app/Services/ScheduledReviewService.php` to encapsulate queue queries and state transitions.
- [X] T005 [P] Add navigation link for "Scheduled Reviews" (المراجعات المجدولة) to the main sidebar menu in `resources/views/partials/sidebar.blade.php`.

---

## Phase 3: User Story 1 - Viewing & Filtering Scheduled Review Appointments (Priority: P1) 🎯 MVP

**Goal**: Display a summary ribbon (Today, Upcoming, Overdue, Completed, Cancelled) and filterable queue table on `/reviews/scheduled`.

**Independent Test**: Seed scheduled review appointments across different statuses and dates, then verify counter badges, tab switching (`TODAY`, `UPCOMING`, `OVERDUE`, `COMPLETED`, `CANCELLED`), and search filtering by patient name or National ID in `tests/Feature/ScheduledReviewTest.php`.

### Implementation for User Story 1

- [X] T006 [P] [US1] Create feature tests for queue listing, status badge counts, and dynamic filtering in `tests/Feature/ScheduledReviewTest.php`.
- [X] T007 [US1] Implement queue query logic and summary counter aggregation (`today`, `upcoming`, `overdue`, `completed`, `cancelled`) in `app/Services/ScheduledReviewService.php` returning paginated `WcScheduledReview` records with `registration.patient` and `dailyReview` relations.
- [X] T008 [US1] Implement `ScheduledReviewController@index` action in `app/Http/Controllers/ScheduledReviewController.php` processing query parameters (`tab`, `search`, `date_from`, `date_to`, `page`).
- [X] T009 [P] [US1] Create Scheduled Reviews dashboard view in `resources/views/reviews/scheduled.blade.php` with summary ribbon cards, RTL Arabic layout, search/filter controls, and appointment queue table.

---

## Phase 4: User Story 2 - Fulfilling a Scheduled Appointment via Daily Review Visit (Priority: P1)

**Goal**: Launch daily review evaluation directly from a pending scheduled appointment, pre-filling evaluation context, and auto-transitioning appointment status to `COMPLETED` when daily review is saved.

**Independent Test**: Select a pending appointment, click "Start Visit", save daily review, and assert `WC_SREV_STATUS` becomes `COMPLETED` with `WC_DREV_SCHEDULED_REVIEW_CD` correctly linked in `tests/Feature/ScheduledReviewTest.php`.

### Implementation for User Story 2

- [X] T010 [P] [US2] Add feature tests for starting fulfillment and auto-completing scheduled review upon daily review creation in `tests/Feature/ScheduledReviewTest.php`.
- [X] T011 [US2] Implement `ScheduledReviewController@startFulfillment` in `app/Http/Controllers/ScheduledReviewController.php` to validate `WC_SREV_STATUS === 'PENDING'` and redirect to `route('daily-reviews.create')` with `registration` and `scheduled_review` query params.
- [X] T012 [US2] Update `DailyReviewController@create` and `resources/views/daily-reviews/create.blade.php` to accept optional `scheduled_review` parameter and render appointment reference context banner.
- [X] T013 [US2] Update `DailyReviewController@store` in `app/Http/Controllers/DailyReviewController.php` to atomically link `WC_DREV_SCHEDULED_REVIEW_CD`, update `WC_SREV_STATUS = 'COMPLETED'`, and set `WC_SREV_COMPLETED_ON = now()` inside a database transaction.

---

## Phase 5: User Story 3 - Manual Appointment Status Management & Cancellation (Priority: P2)

**Goal**: Allow clinic staff to reschedule appointment date or manually cancel a pending appointment with optional cancellation notes.

**Independent Test**: Reschedule target date for a pending appointment and verify date updates while keeping status `PENDING`; cancel appointment with reason notes and verify status updates to `CANCELLED` in `tests/Feature/ScheduledReviewTest.php`.

### Implementation for User Story 3

- [X] T014 [P] [US3] Add feature tests for rescheduling appointment date and manual cancellation in `tests/Feature/ScheduledReviewTest.php`.
- [X] T015 [P] [US3] Create modal dialog partials `resources/views/reviews/partials/reschedule-modal.blade.php` and `resources/views/reviews/partials/cancel-modal.blade.php`.
- [X] T016 [US3] Implement `ScheduledReviewController@reschedule` in `app/Http/Controllers/ScheduledReviewController.php` validating `scheduled_date` (`required|date`) and updating `WC_SREV_SCHEDULED_DATE` for `PENDING` appointments.
- [X] T017 [US3] Implement `ScheduledReviewController@cancel` in `app/Http/Controllers/ScheduledReviewController.php` validating `notes` (`nullable|string|max:1000`) and updating `WC_SREV_STATUS = 'CANCELLED'`.

---

## Phase 6: User Story 4 - Hard Deletion of Old Pending Appointments on Re-Registration (Priority: P2)

**Goal**: Automatically hard-delete all `PENDING` scheduled review records belonging to previous registrations of a patient when a new active registration is created.

**Independent Test**: Create an old registration with pending scheduled reviews, create and accept a new registration for the same patient, and assert that old `PENDING` rows are permanently removed (`DELETE FROM wc_scheduled_review_tb`) in `tests/Feature/ScheduledReviewTest.php`.

### Implementation for User Story 4

- [X] T018 [P] [US4] Add feature tests for hard deletion of old pending appointments on re-registration in `tests/Feature/ScheduledReviewTest.php`.
- [X] T019 [US4] Implement `ScheduledReviewService::deletePendingForPreviousRegistrations($patientCode, $newRegistrationCode)` in `app/Services/ScheduledReviewService.php` executing `WcScheduledReview::whereIn('WC_SREV_REGISTRATION_CD', $oldRegCodes)->where('WC_SREV_STATUS', 'PENDING')->delete()`.
- [X] T020 [US4] Integrate re-registration cleanup logic into registration acceptance handler in `app/Http/Controllers/RegistrationRequestController.php` inside the registration creation transaction.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Full verification, code style formatting, and scenario validation.

- [X] T021 [P] Execute full test suite `php artisan test --filter=ScheduledReviewTest` and verify 100% pass rate.
- [X] T022 [P] Format codebase using Pint formatter: `vendor/bin/pint --dirty --format agent`.
- [X] T023 Run validation scenarios from `specs/007-scheduled-reviews/quickstart.md`.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately.
- **Foundational (Phase 2)**: Depends on Setup (Phase 1) - BLOCKS user stories.
- **User Story 1 (Phase 3 - P1)**: Depends on Foundational (Phase 2).
- **User Story 2 (Phase 4 - P1)**: Depends on Foundational (Phase 2) and US1 dashboard endpoint.
- **User Story 3 (Phase 5 - P2)**: Depends on Foundational (Phase 2) and US1 dashboard view.
- **User Story 4 (Phase 6 - P2)**: Depends on Foundational (Phase 2) and registration approval workflow.
- **Polish (Phase 7)**: Depends on completion of user stories.

### Parallel Opportunities

- `T003`, `T004`, `T005` in Foundational Phase can run in parallel.
- Test tasks marked `[P]` (`T006`, `T010`, `T014`, `T018`) can be implemented in parallel within their respective story phases.
- Blade modal views `T015` [P] can be created in parallel with controller logic.
