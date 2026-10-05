# Tasks: Phase 10 — Wound Photo Documentation (توثيق صور الجروح)

**Feature**: Wound Photo Documentation (Phase 10) | **Branch**: `010-wound-photo-documentation` | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/010-wound-photo-documentation/spec.md)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Storage setup and directory initialization

- [x] T001 Configure storage directory `storage/app/public/wound_photos` and verify `php artisan storage:link` setup

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core database table and Eloquent model setup that MUST be complete before ANY user story can be implemented

- [x] T002 Create database table `wc_wound_photo_tb` with fields: `WC_PHOTO_CODE` (`CHAR(36)` PK), `WC_PHOTO_REG_DIAGNOSIS_CD` (`CHAR(36)` FK, Not Null), `WC_PHOTO_DAILY_REVIEW_DETAIL_CD` (`CHAR(36)` FK, Nullable), `WC_PHOTO_STAGE` (`VARCHAR(20)` Not Null), `WC_PHOTO_PATH` (`VARCHAR(255)` Not Null), `WC_PHOTO_CAPTION` (`TEXT` Nullable), `WC_PHOTO_TAKEN_AT` (`DATETIME` Not Null), `WC_PHOTO_NURSE_CD` (`CHAR(36)` FK Not Null), `WC_PHOTO_ENTRY_USER_CD` (`CHAR(36)` FK Not Null)
- [x] T003 [P] Create Eloquent model `App\Models\WcWoundPhoto` in `app/Models/WcWoundPhoto.php` with `$table = 'wc_wound_photo_tb'`, `$primaryKey = 'WC_PHOTO_CODE'`, `$timestamps = false`, `$keyType = 'string'`, `$incrementing = false`, and relationships (`regDiagnosis`, `dailyReviewDetail`, `nurse`, `entryUser`)

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Initial Wound Photo Baseline Documentation (Priority: P1) 🌟 MVP

**Goal**: Allow nurses to capture/upload one or more optional baseline photos for each wound diagnosis during registration request acceptance.

**Independent Test**: Accept a registration request with wound diagnosis, upload initial wound photo(s), and verify records in `wc_wound_photo_tb` with `WC_PHOTO_STAGE = 'INITIAL'`.

### Implementation for User Story 1

- [x] T004 [P] [US1] Update `AcceptRegistrationRequest` in `app/Http/Requests/AcceptRegistrationRequest.php` to validate optional `diagnoses.*.wound_photos.*` file array (`file|mimes:jpeg,jpg,png,webp|max:5120`)
- [x] T005 [US1] Update `RegistrationRequestController@accept` in `app/Http/Controllers/RegistrationRequestController.php` transaction to store uploaded files in `storage/app/public/wound_photos/{YYYY}/{MM}/{uuid}.{ext}` and insert `WcWoundPhoto` records (`WC_PHOTO_STAGE = 'INITIAL'`)
- [x] T006 [US1] Update registration acceptance form modal in `resources/views/requests/show.blade.php` to include file input / camera capture for initial wound photos per diagnosis item

**Checkpoint**: At this point, User Story 1 is fully functional and testable independently.

---

## Phase 4: User Story 2 - Follow-Up Wound Photo Capture During Daily Reviews (Priority: P1)

**Goal**: Allow nurses to capture/upload one or more optional photos for each wound assessment item during daily reviews.

**Independent Test**: Record a daily review, attach follow-up photo(s) for a wound item, and verify records in `wc_wound_photo_tb` with `WC_PHOTO_STAGE = 'REVIEW'` linked to both `WC_REG_DIAGNOSIS_TB` and `WC_DAILY_REVIEW_DETAIL_TB`.

### Implementation for User Story 2

- [x] T007 [P] [US2] Update `StoreDailyReviewRequest` in `app/Http/Requests/StoreDailyReviewRequest.php` to validate optional `details.*.wound_photos.*` file array (`file|mimes:jpeg,jpg,png,webp|max:5120`)
- [x] T008 [US2] Update `DailyReviewController@store` in `app/Http/Controllers/DailyReviewController.php` transaction to store uploaded review files in `storage/app/public/wound_photos/{YYYY}/{MM}/{uuid}.{ext}` and insert `WcWoundPhoto` records (`WC_PHOTO_STAGE = 'REVIEW'`)
- [x] T009 [US2] Update daily review creation form in `resources/views/daily_reviews/create.blade.php` to include file input / camera capture for review wound photos per wound item

**Checkpoint**: User Story 1 AND 2 work independently.

---

## Phase 5: User Story 3 - Interactive Visual Progression Timeline & Gallery (Priority: P2)

**Goal**: Display an interactive chronological photo timeline and side-by-side comparison modal on the patient's registration details page.

**Independent Test**: Navigate to `/registrations/{id}`, open the Visual Progression tab, click a review photo, and verify side-by-side comparison with the initial baseline photo.

### Implementation for User Story 3

- [x] T010 [P] [US3] Create media Controller `WoundPhotoController` in `app/Http/Controllers/WoundPhotoController.php` and route `GET /wound-photos/{photo}` in `routes/web.php` to serve photo files securely
- [x] T011 [P] [US3] Create Blade partial `resources/views/registrations/partials/wound_photo_timeline.blade.php` rendering chronological photo progression timeline per wound diagnosis
- [x] T012 [US3] Integrate `wound_photo_timeline` component and side-by-side comparison modal into patient registration details page `resources/views/registrations/show.blade.php`

**Checkpoint**: All user stories fully functional.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Testing, code formatting, and verification

- [x] T013 [P] Create automated PHPUnit feature test `tests/Feature/WoundPhotoDocumentationTest.php` testing initial photo upload, review photo upload, and timeline photo access
- [x] T014 Run `vendor/bin/pint` formatting and execute test suite `php artisan test --compact --filter=WoundPhotoDocumentationTest`
- [x] T015 Verify end-to-end quickstart validation scenarios in `specs/010-wound-photo-documentation/quickstart.md`

---

## Dependencies & Execution Order

```
Phase 1 (Setup) → Phase 2 (Foundational)
                        ↓
             Phase 3 (User Story 1 - MVP)
                        ↓
             Phase 4 (User Story 2)
                        ↓
             Phase 5 (User Story 3)
                        ↓
             Phase 6 (Polish & Testing)
```

---

## Parallel Execution Opportunities

- T003 (`WcWoundPhoto` model) can be built in parallel with T002 table creation.
- T004 (`AcceptRegistrationRequest`) and T007 (`StoreDailyReviewRequest`) form validations can be built in parallel.
- T010 (`WoundPhotoController`) and T011 (`wound_photo_timeline.blade.php`) can be built in parallel.
