---
description: "Actionable tasks for Phase 5A longitudinal patient medical record"
---

# Tasks: Longitudinal Patient Medical Record

**Input**: Design documents from `/specs/011-longitudinal-patient-record/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/patient-medical-record-ui.md](./contracts/patient-medical-record-ui.md), [quickstart.md](./quickstart.md)

**Tests**: Feature tests are included because the specification defines mandatory independent tests and the implementation plan requires focused regression and query-growth coverage.

**Organization**: Tasks are grouped by user story so each story can be implemented and verified as an independent increment.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel after its phase prerequisites because it changes a different file and does not depend on unfinished work.
- **[Story]**: Maps the task to a user story in [spec.md](./spec.md).
- Every task names the exact file or files it changes or validates.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare the existing Laravel/Vite application for the patient-history page asset without adding dependencies or directories outside the approved structure.

- [X] T001 Register `resources/css/pages/patients.css` as a Vite input in `vite.config.js` and create the page-scoped stylesheet at `resources/css/pages/patients.css`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Establish the reusable view and test foundations shared by all four stories.

**⚠️ CRITICAL**: Complete this phase before implementing any user story.

- [X] T002 [P] Parameterize `resources/views/reviews/partials/review_timeline.blade.php` with backward-compatible defaults for a registration-prefixed section ID, action visibility, baseline-photo visibility, and initial disclosure; prefix every nested HTML ID while preserving current `resources/views/registrations/show.blade.php` behavior
- [X] T003 [P] Create `tests/Feature/PatientMedicalRecordTest.php` with the existing PHPUnit, JWT authentication helper, UUID, direct-model fixture, and `RefreshDatabase` conventions needed by all story tests

**Checkpoint**: The patient-history asset, reusable review contract, and test harness are ready.

---

## Phase 3: User Story 1 — View the Complete Registration History (Priority: P1) 🎯 MVP

**Goal**: Show every registration belonging to the patient on one authorized page, with current episodes first, deterministic ordering, clear summaries, and correct initial expansion.

**Independent Test**: Open a patient with one active and two discharged registrations. All registrations appear once, the newest active registration is first and open, historical registrations follow newest first, and a patient with no active registration opens the newest historical episode.

### Tests for User Story 1

- [X] T004 [US1] Add failing feature tests for existing JWT protection, active-first and date-plus-code tie ordering, newest-active/default-historical expansion, patient demographics rendered once, single-registration behavior, and the zero-registration empty state in `tests/Feature/PatientMedicalRecordTest.php`

### Implementation for User Story 1

- [X] T005 [US1] Refactor `app/Http/Controllers/PatientMedicalRecordController.php` to retrieve registrations only through the selected patient's relationship, eager-load department, procedure, nurse, entry user, and discharging nurse, add wound/review/appointment counts, order active episodes first then `WC_REG_DATE DESC` and `WC_REG_CODE DESC`, and identify the first ordered episode as initially open
- [X] T006 [P] [US1] Create the native `<details>` registration shell in `resources/views/patients/partials/registration_record.blade.php` with unique registration-based IDs, `data-registration-code`, `data-registration-status`, textual status, date, department, responsible nurse, optional procedure/discharge data, section counts, and initial-open state
- [X] T007 [US1] Refactor `resources/views/patients/show.blade.php` to load the patient stylesheet, render `resources/views/patients/partials/patient_demographics_banner.blade.php` once, show the history count, render each ordered registration through `registration_record.blade.php`, and show one Arabic patient-level empty state when no registrations exist
- [X] T008 [P] [US1] Style the outer registration disclosures, current/historical states, stacked mobile summary, LTR dates/codes, visible focus, and Arabic RTL hierarchy in `resources/css/pages/patients.css`

**Checkpoint**: User Story 1 is independently usable and all registration-order and empty-state tests pass.

---

## Phase 4: User Story 2 — Inspect a Previous Registration in Context (Priority: P1)

**Goal**: Expand any registration and inspect only its wounds, appointments, reviews, treatments, and images without cross-registration leakage or duplicate clinical items.

**Independent Test**: Use two registrations with different wounds, reviews, appointments, baseline photos, and review photos. Expanding both shows every item once under its owner; reviews and appointments have stable newest-first order; missing sections show clear empty states.

### Tests for User Story 2

- [X] T009 [US2] Add failing feature tests for cross-patient and cross-registration isolation, review and appointment date-plus-code ordering, scheduled versus unscheduled labels, all stored appointment states, complete review/wound fields, baseline-photo uniqueness, follow-up-photo ownership, duplicate prevention, and missing-section empty states in `tests/Feature/PatientMedicalRecordTest.php`

### Implementation for User Story 2

- [X] T010 [US2] Extend `app/Http/Controllers/PatientMedicalRecordController.php` with constrained eager loading for diagnoses, wound types, daily reviews, review details, dressings, medications, scheduled reviews, baseline photos, and review-owned photos; retain required UUID/FK columns, reject mismatched registration links, order reviews by `WC_DREV_VISIT_DATE DESC, WC_DREV_CODE DESC`, appointments by `WC_SREV_SCHEDULED_DATE DESC, WC_SREV_CODE DESC`, and avoid lazy accessors or view-time queries
- [X] T011 [P] [US2] Create `resources/views/patients/partials/registration_wounds.blade.php` to render every registration wound and diagnosis, optional clinical observations, and each initial photo exactly once with authenticated `wound_photos.show` URLs, wound-specific Arabic alt text, and `loading="lazy"`
- [X] T012 [P] [US2] Create `resources/views/patients/partials/registration_appointments.blade.php` to render scheduled date/time, textual state, notes, fulfillment context, stable order, and an Arabic empty state without mixing appointments from other registrations
- [X] T013 [P] [US2] Extend `resources/views/reviews/partials/review_timeline.blade.php` to render visit type/date, scheduled-versus-unscheduled distinction, outcome, notes, per-wound identity and observation, dressing, medication, wound outcome, and only the photos owned by that review detail; provide a baseline comparison reference without repeating the baseline image
- [X] T014 [P] [US2] Ensure pending, completed, cancelled, missed, and voided appointment values have readable Arabic text in `resources/views/components/status-badge.blade.php` while preserving existing status mappings
- [X] T015 [US2] Compose wounds, appointments, and the parameterized review timeline inside `resources/views/patients/partials/registration_record.blade.php`, pass registration-prefixed IDs and read-only flags, and provide separate Arabic empty states for wounds, reviews, appointments, and photos without suppressing other sections
- [X] T016 [P] [US2] Add responsive wound, appointment, review, baseline-reference, and lazy-image presentation styles to `resources/css/pages/patients.css`, reusing the established clinical classes from `resources/css/pages/registrations.css` where possible

**Checkpoint**: User Stories 1 and 2 form a clinically safe longitudinal view with no mixed or duplicated records.

---

## Phase 5: User Story 3 — Navigate Between Patient History and a Registration (Priority: P2)

**Goal**: Open the correct dedicated registration workflow from any episode and return to the complete patient history while historical content stays read-only in the aggregate view.

**Independent Test**: Follow the dedicated-record link from a discharged historical episode, verify the expected registration and status-appropriate actions, then return to the same patient's ordered longitudinal record.

### Tests for User Story 3

- [X] T017 [US3] Add failing feature tests for each episode's correct `registrations.show` URL, absence of add/edit controls in the aggregate historical content, preservation of dedicated-page status rules, and the patient-history return URL in `tests/Feature/PatientMedicalRecordTest.php`

### Implementation for User Story 3

- [X] T018 [P] [US3] Add a reachable Arabic “open detailed record” link for the owning registration and keep all mutation calls to action hidden in aggregate mode in `resources/views/patients/partials/registration_record.blade.php`
- [X] T019 [P] [US3] Add or normalize the `patients.show` return link for the owning patient in `resources/views/registrations/show.blade.php` without changing existing registration-status action guards

**Checkpoint**: User Story 3 is independently navigable through existing named routes with no new endpoint.

---

## Phase 6: User Story 4 — Review Clinical Accountability Across Time (Priority: P2)

**Goal**: Display the clinical employee and data-entry user separately for every event where both identities are available, attached to the correct episode.

**Independent Test**: Render registrations, wounds, reviews, and photos performed by one employee and entered by another user. Both identities appear under the correct event and never inherit across registrations.

### Tests for User Story 4

- [X] T020 [US4] Add failing feature tests for separate clinical-employee and data-entry-user labels on registration, wound/diagnosis, daily review, and wound-photo events across multiple registrations in `tests/Feature/PatientMedicalRecordTest.php`

### Implementation for User Story 4

- [X] T021 [US4] Complete audit-relation eager loading in `app/Http/Controllers/PatientMedicalRecordController.php` for registration entry/discharge identities, diagnosis clinical employee and entry user, review nurse and entry user, scheduled-review creator, and baseline/review-photo nurse and entry user
- [X] T022 [P] [US4] Render wound and baseline-photo clinical employees separately from data-entry users in `resources/views/patients/partials/registration_wounds.blade.php`, using the established neutral placeholder when an optional identity is unavailable
- [X] T023 [P] [US4] Render daily-review and follow-up-photo clinical employees separately from data-entry users in `resources/views/reviews/partials/review_timeline.blade.php` without inheriting registration-level identities
- [X] T024 [P] [US4] Render registration entry/discharge identities in `resources/views/patients/partials/registration_record.blade.php` and scheduled-review creator information in `resources/views/patients/partials/registration_appointments.blade.php`

**Checkpoint**: All four stories work independently and the complete record preserves clinical accountability.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Validate performance, accessibility, RTL behavior, existing workflows, and the complete acceptance contract.

- [X] T025 Add query-growth, duplicate DOM ID, unique clinical-item/photo rendering, and lazy-image attribute assertions for a representative 10-registration/200-review record in `tests/Feature/PatientMedicalRecordTest.php`; compare bounded relationship-query growth instead of asserting one brittle global query count
- [X] T026 Refine `:focus-visible`, narrow-screen stacking, dark-mode, and reduced-motion behavior for all patient-history disclosures and links in `resources/css/pages/patients.css`
- [X] T027 Run the focused and related PHPUnit commands, `vendor/bin/pint --dirty --format agent`, `npm run build`, Blade view cache validation, and then the full suite exactly as documented in `specs/011-longitudinal-patient-record/quickstart.md`
- [X] T028 Manually validate Arabic RTL keyboard navigation, the three-interaction discoverability target, photo placement, all empty states, dedicated-page round trip, and the under-two-second representative record target using `specs/011-longitudinal-patient-record/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 — Setup**: No dependencies.
- **Phase 2 — Foundational**: Depends on T001 and blocks all user stories.
- **Phase 3 — US1**: Depends on Phase 2 and establishes the longitudinal registration shell.
- **Phase 4 — US2**: Depends on US1's registration shell; it completes the second P1 clinical-safety slice.
- **Phase 5 — US3**: Depends on the US1 shell but can run in parallel with US2 after US1 if file conflicts are coordinated.
- **Phase 6 — US4**: Depends on the rendered event partials from US2.
- **Phase 7 — Polish**: Depends on every story selected for delivery.

### User Story Dependencies

- **US1 (P1)**: Starts after Foundational and has no story dependency.
- **US2 (P1)**: Depends on US1 because it fills the registration shell, while its ownership and photo tests remain independently runnable.
- **US3 (P2)**: Depends on US1 only; it can be delivered before US2/US4.
- **US4 (P2)**: Depends on US2 because audit identities are rendered within the event partials created there.

### Within Each User Story

1. Add the story's failing feature tests.
2. Complete controller/data retrieval work.
3. Implement independent partials marked `[P]`.
4. Compose the story UI.
5. Run the story-focused test filter before continuing.

### Parallel Opportunities

- T002 and T003 can run together after T001.
- T006 and T008 can run together after T005 establishes the view contract.
- T011, T012, T013, T014, and T016 can run together after T010.
- US3 can proceed in parallel with US2 after US1, with T018 coordinated against US2's later edits to `registration_record.blade.php`.
- T022, T023, and T024 can run together after T021.
- T025 and T026 can run together after all selected stories are complete.

---

## Parallel Examples

### User Story 1

```text
Task T006: Create resources/views/patients/partials/registration_record.blade.php
Task T008: Style registration disclosures in resources/css/pages/patients.css
```

### User Story 2

```text
Task T011: Create registration_wounds.blade.php
Task T012: Create registration_appointments.blade.php
Task T013: Extend review_timeline.blade.php
Task T014: Complete status-badge mappings
Task T016: Add inner clinical-section styles
```

### User Story 3

```text
Task T018: Add the dedicated-record link in the patient registration partial
Task T019: Add the patient-history return link in the dedicated registration view
```

### User Story 4

```text
Task T022: Render wound and baseline-photo identities
Task T023: Render review and follow-up-photo identities
Task T024: Render registration and appointment identities
```

---

## Implementation Strategy

### MVP First

1. Complete Setup and Foundational tasks.
2. Complete US1 and run its focused tests.
3. Demonstrate that every registration is visible, ordered, and expandable from the patient page.

US1 is the smallest demonstrable MVP. Complete US2 before production use because registration ownership, clinical detail isolation, and photo uniqueness are safety-critical P1 requirements.

### Incremental Delivery

1. **US1**: Complete longitudinal registration discovery and ordering.
2. **US2**: Add clinically isolated wounds, reviews, appointments, and images.
3. **US3**: Add the dedicated-workflow round trip.
4. **US4**: Complete audit identity presentation.
5. **Polish**: Validate query growth, accessibility, RTL, builds, tests, and representative performance.

### Parallel Team Strategy

After the shared foundation and US1 shell are complete:

- One contributor can build US2 clinical partials.
- Another can complete US3 navigation.
- US4 begins after the US2 event partials stabilize.
- CSS and test work marked `[P]` can proceed alongside distinct view files.

## Notes

- Keep all historical aggregate content read-only.
- Do not create migrations, new routes, caches, or dependencies for this feature unless measured evidence changes the approved plan.
- Do not query from Blade or use the `WcRegDiagnosis::latest_detail` lazy accessor on the longitudinal page.
- Preserve UUID primary/foreign keys in constrained eager-load selects.
- Use named routes for registration and wound-photo links.
- Commit after each task or logical task group.



