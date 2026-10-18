---

description: "Dependency-ordered implementation tasks for Phase 5B structured wound assessment"
---

# Tasks: Phase 5B — Structured Wound Assessment & Hospital Form Parity

**Input**: Design documents from `/specs/012-structured-wound-assessment/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/`, `quickstart.md`

**Tests**: PHPUnit feature and MySQL procedure-integration tests are required by the specification, implementation plan, and constitution. Write each story’s tests first and verify that they fail for the missing behavior before implementation.

**Organization**: Tasks are grouped by user story so each clinical increment can be implemented and validated independently where its documented dependencies permit.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel because it changes different files and has no dependency on another incomplete task in the phase.
- **[Story]**: Maps the task to a user story in `spec.md`.
- Every task names the file or files it changes.

## Phase 1: Setup

**Purpose**: Prepare stable file boundaries without changing dependencies.

- [ ] T001 Verify PHP/Node/universal exclusions remain complete for the Laravel/Vite project and append only missing critical patterns in `.gitignore`
- [ ] T002 [P] Create the clinical workflow interface and method signatures for complete registration acceptance, complete wound addition, and review completion in `app/Services/ClinicalWorkflows/ClinicalWorkflowGatewayInterface.php`
- [ ] T003 [P] Create migration scaffolds with `php artisan make:migration --no-interaction` at `database/migrations/2026_10_12_000001_create_structured_wound_assessment_tables.php`, `database/migrations/2026_10_12_000002_add_structured_context_to_wound_workflows.php`, and `database/migrations/2026_10_12_000003_install_structured_wound_workflow_procedures.php`

---

## Phase 2: Foundational Data and Workflow Boundaries

**Purpose**: Build the schema, models, vocabulary, and database-call boundary required by every user story.

**⚠️ CRITICAL**: No user-story implementation starts until this phase passes its focused tests.

### Tests

- [ ] T004 Create failing schema/model behavior tests for DB-first keys, explicit relationships, choice retirement readability, category scoping, PMH uniqueness, and legacy-null compatibility in `tests/Feature/WoundAssessmentModelTest.php`

### Implementation

- [ ] T005 Implement `WC_ASSESSMENT_CHOICE_TB` and `WC_REGISTRATION_PMH_TB` in `database/migrations/2026_10_12_000001_create_structured_wound_assessment_tables.php` with UUID `CHAR(36)` keys, unique `(WC_ACH_CATEGORY, WC_ACH_KEY)`, `WC_ACH_IS_ACTIVE` restricted to active/inactive lifecycle, deterministic display order, unique `(WC_RPMH_REGISTRATION_CD, WC_RPMH_PMH_CD)`, and exact foreign keys from `data-model.md`
- [ ] T006 Implement `WC_WOUND_ASSESSMENT_TB` and `WC_ASSESSMENT_FINDING_TB` in `database/migrations/2026_10_12_000001_create_structured_wound_assessment_tables.php`: `INITIAL` requires no review detail, `FOLLOW_UP` requires one unique review detail, length/width are `DECIMAL(8,2)` values `> 0` and `<= 1000`, depth is `>= 0` and `<= 1000`, findings are unique per assessment/choice, and all indexes/foreign keys match `data-model.md`
- [ ] T007 Extend populated workflow tables safely in `database/migrations/2026_10_12_000002_add_structured_context_to_wound_workflows.php`: legacy registration allergy/comorbidity/follow-up values remain nullable, new allergy description is required when allergies are present and empty otherwise, PMH links are forbidden when no-comorbidities is true, follow-up objective is required with date/frequency, `WC_DREV_SUBMISSION_KEY` is nullable for legacy but unique and required for new visits, non-null scheduled-review linkage is unique, and assessment-linked photos remain nullable for legacy
- [ ] T008 [P] Create the DB-first choice and finding models with explicit table, key, timestamp, fillable, casts, and typed relationships in `app/Models/WcAssessmentChoice.php` and `app/Models/WcAssessmentFinding.php`
- [ ] T009 [P] Create the DB-first assessment and registration-PMH models with explicit table, key, timestamp, fillable, casts, and typed relationships in `app/Models/WcWoundAssessment.php` and `app/Models/WcRegistrationPmh.php`
- [ ] T010 Extend existing typed relationships and fillable/cast definitions for structured context in `app/Models/WcRegistration.php`, `app/Models/WcRegDiagnosis.php`, `app/Models/WcDailyReviewDetail.php`, `app/Models/WcDailyReview.php`, `app/Models/WcWoundPhoto.php`, and `app/Models/WcPmh.php`
- [ ] T011 Obtain and record clinical-owner approval for every stable meaning and final Arabic label in `specs/012-structured-wound-assessment/contracts/hospital-form-field-matrix.md` before creating deterministic category/key seed data, including `WOUND_MARGIN.NONE_OBSERVED` and `WOUND_MARGIN.POCKETING`, in `database/seeders/WcAssessmentChoiceSeeder.php` and registering it in `database/seeders/DatabaseSeeder.php`; never overwrite a used historical meaning
- [ ] T012 Implement the production JSON procedure caller and normalized result/error mapping in `app/Services/ClinicalWorkflows/MySqlClinicalWorkflowGateway.php`
- [ ] T013 Implement the deterministic SQLite testing contract with the same aggregate invariants and transaction boundary in `app/Services/ClinicalWorkflows/SqliteClinicalWorkflowGateway.php`
- [ ] T014 Bind the MySQL gateway in normal environments and SQLite gateway only when the testing database connection is SQLite in `app/Providers/AppServiceProvider.php`
- [ ] T015 Run `tests/Feature/WoundAssessmentModelTest.php` and fix the foundational schema/model implementation until it passes

**Checkpoint**: Canonical assessment storage, stable choices, compatibility columns, and the workflow gateway are ready.

---

## Phase 3: User Story 1 — Complete Initial Assessment for Every Wound (Priority: P1) 🎯 MVP

**Goal**: Accept a request or add a wound only when every included diagnosis has a complete isolated baseline, shared registration context, treatment, audit identities, and optional photos.

**Independent Test**: Accept a request containing three diagnoses with different values/photos, reopen the registration, and verify one shared context plus three isolated complete baselines; incomplete input creates no registration.

### Tests for User Story 1

- [ ] T016 [P] [US1] Create failing HTTP tests for three-wound acceptance, complete-field enforcement, preserved Arabic wound-qualified errors, allergies/PMH exclusivity, shared follow-up scheduling, measurement boundaries, dry/exudate validation, and dual audit in `tests/Feature/StructuredInitialAssessmentTest.php`
- [ ] T017 [P] [US1] Create failing tests proving post-registration wound creation requires a complete baseline and rejects discharged registration, inactive/wrong-category choices, and cross-domain references in `tests/Feature/StructuredWoundAdditionTest.php`
- [ ] T018 [P] [US1] Extend failing baseline-photo tests in `tests/Feature/WoundPhotoDocumentationTest.php` for diagnosis isolation, canonical assessment ownership, storage failure preventing the clinical procedure call, final-path existence before metadata commit, and cleanup of prewritten files when the procedure rejects the aggregate

### Implementation for User Story 1

- [ ] T019 [US1] Implement nested initial-assessment validation and Arabic attributes in `app/Http/Requests/AcceptRegistrationRequest.php`: every diagnosis requires the initial fields in `contracts/web-workflows.md`, length/width `>0 <=1000`, depth `>=0 <=1000`, at most two decimals, `DRY` forbids exudate type, allergies require description when present, PMH “None” is exclusive, and at least one date/frequency plus objective forms the shared plan
- [ ] T020 [US1] Implement complete added-wound validation using the same diagnosis constraints, treatment requirement, active category-qualified choices, and photo rules in `app/Http/Requests/StoreWoundDiagnosisRequest.php`
- [ ] T021 [US1] Implement `SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS` and `SP_WC_ADD_WOUND_WITH_ASSESSMENT` in `database/migrations/2026_10_12_000003_install_structured_wound_workflow_procedures.php` so shared context, PMH, registration/diagnoses, one baseline per diagnosis, findings, scheduled plan, photo metadata, actor, entry user, and timestamp commit atomically or not at all
- [ ] T022 [US1] Replace controller-side acceptance and added-diagnosis row orchestration with `ClinicalWorkflowGatewayInterface` calls in `app/Http/Controllers/RegistrationRequestController.php` and `app/Http/Controllers/RegistrationController.php`: write validated photos to immutable final UUID-based paths before the gateway call, pass only persisted path metadata, delete those new files if the gateway fails, and perform no required post-commit rename
- [ ] T023 [P] [US1] Build reusable Arabic RTL initial wound fields and incomplete-panel summaries in `resources/views/wound_assessments/partials/fields.blade.php` and `resources/views/wound_assessments/partials/latest-comparison.blade.php`
- [ ] T024 [US1] Replace the minimal diagnosis rows with shared registration context plus repeatable complete wound cards while preserving old input/errors in `resources/views/requests/show.blade.php`
- [ ] T025 [US1] Replace the minimal post-registration diagnosis modal with the complete baseline component in `resources/views/registrations/partials/wound_diagnosis_modal.blade.php`
- [ ] T026 [P] [US1] Implement repeatable diagnosis cards, exclusive choice behavior, wound error navigation, measurement helpers, and per-card photo ownership in `resources/js/pages/requests.js`
- [ ] T027 [P] [US1] Style initial assessment grouping and narrow-screen RTL behavior using existing design tokens in `resources/css/pages/requests.css` and `resources/css/pages/registrations.css`
- [ ] T028 [US1] Run `tests/Feature/StructuredInitialAssessmentTest.php`, `tests/Feature/StructuredWoundAdditionTest.php`, and the affected baseline cases in `tests/Feature/WoundPhotoDocumentationTest.php`; fix US1 until all pass

**Checkpoint**: A clinically complete structured baseline exists for every wound before registration acceptance or later wound addition.

---

## Phase 4: User Story 2 — Structured Daily Follow-Up Per Wound (Priority: P1)

**Goal**: Complete one daily visit with a shared header and a required structured assessment for every registration diagnosis.

**Independent Test**: Submit one daily visit for two wounds with distinct findings/treatments/photos and verify one header, two compatible detail snapshots, two isolated canonical assessments, and idempotent retry behavior.

### Tests for User Story 2

- [ ] T029 [P] [US2] Create failing daily-review tests for exact diagnosis coverage, duplicate diagnosis rejection, cross-registration ownership, dry/exudate conflict, optional follow-up measurements, inactive/wrong-category choices, authoritative `WC_REVIEW_DETAIL_TB.WC_DREVD_STATUS` outcome access through the linked assessment, legacy detail snapshots, actor/entry separation, and idempotent replay in `tests/Feature/StructuredDailyReviewTest.php`
- [ ] T030 [P] [US2] Extend failing review-photo tests in `tests/Feature/WoundPhotoDocumentationTest.php` for canonical assessment/detail/diagnosis ownership, storage failure preventing the clinical procedure call, final-path existence before metadata commit, and cleanup of prewritten files when the procedure rejects the aggregate

### Implementation for User Story 2

- [ ] T031 [US2] Expand daily/scheduled nested payload validation, Arabic wound-qualified attributes, exact registration-diagnosis coverage, UUID submission key, treatment rules, and shared follow-up consistency in `app/Http/Requests/StoreDailyReviewRequest.php`
- [ ] T032 [US2] Implement the daily branch of `SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS` in `database/migrations/2026_10_12_000003_install_structured_wound_workflow_procedures.php`, repeating ownership/category/activity/measurement/dry-exudate checks and atomically writing the visit, compatibility details with the sole outcome value in `WC_DREVD_STATUS`, linked assessments without duplicate outcome storage, findings, photo metadata, audit identities, and optional next appointment
- [ ] T033 [US2] Refactor daily review creation/storage in `app/Http/Controllers/DailyReviewController.php` to eager-load the latest assessment per diagnosis, issue a submission key, write validated photos to immutable final UUID-based paths before invoking the workflow gateway, pass only persisted path metadata, delete those new files if the gateway fails, and perform no required post-commit rename
- [ ] T034 [P] [US2] Build the reusable structured follow-up panel with read-only wound identity, latest comparison, required daily fields, optional margins/measurements, treatment, narratives, and photos in `resources/views/wound_assessments/partials/follow-up-fields.blade.php`
- [ ] T035 [US2] Integrate the follow-up component for every diagnosis and preserve all old input/errors in `resources/views/reviews/daily_create.blade.php`
- [ ] T036 [P] [US2] Implement dry/exudate toggling, exclusive margin “None,” diagnosis completion summary, double-submit guard, and responsive interactions in `resources/js/pages/reviews.js`
- [ ] T037 [P] [US2] Style latest-comparison blocks, structured field groups, error summaries, and narrow-screen diagnosis cards in `resources/css/pages/reviews.css`
- [ ] T038 [US2] Run `tests/Feature/StructuredDailyReviewTest.php` and affected follow-up-photo cases in `tests/Feature/WoundPhotoDocumentationTest.php`; fix US2 until all pass

**Checkpoint**: Daily visits capture a complete, comparable, isolated assessment for every wound.

---

## Phase 5: User Story 3 — Scheduled Review with Daily Dataset Parity (Priority: P1)

**Goal**: Complete a pending scheduled appointment through the same diagnosis-specific clinical contract while preserving appointment linkage and state safety.

**Independent Test**: Complete a pending appointment for an older registration, verify daily-field parity and one linked visit, then prove repeat, wrong-registration, missed, and cancelled submissions are rejected.

### Tests for User Story 3

- [ ] T039 [US3] Create failing scheduled-review tests for form parity, older-registration support, pending-only completion, cross-registration diagnosis rejection, unique visit linkage, repeat completion, and shared next appointment in `tests/Feature/StructuredScheduledReviewTest.php`

### Implementation for User Story 3

- [ ] T040 [US3] Complete the scheduled branch of `SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS` in `database/migrations/2026_10_12_000003_install_structured_wound_workflow_procedures.php` so only the matching pending appointment transitions once to completed and all diagnosis assessments use the same clinical dataset as daily reviews
- [ ] T041 [US3] Enforce route-owned appointment/registration identity and eligible state while reusing the daily form data and gateway payload in `app/Http/Controllers/DailyReviewController.php`
- [ ] T042 [US3] Update scheduled-review entry points and status messaging without duplicating clinical fields in `resources/views/reviews/scheduled.blade.php` and `resources/views/reviews/daily_create.blade.php`
- [ ] T043 [US3] Run `tests/Feature/StructuredScheduledReviewTest.php` and affected cases in `tests/Feature/ScheduledReviewTest.php`; fix US3 until all pass

**Checkpoint**: Daily and fulfilled scheduled reviews have full field and validation parity.

---

## Phase 6: User Story 4 — Compare Wound Progress with Accountability (Priority: P2)

**Goal**: Show baseline and follow-up structured states chronologically under the correct wound and registration, with audit identities, photos, and legacy fallback.

**Independent Test**: Render a wound with initial, daily, scheduled, and legacy events and verify chronology, measurements, treatment, clinical actor, entry user, timestamp, photo ownership, inactive labels, and `لم يُسجل` fallbacks.

### Tests for User Story 4

- [ ] T044 [US4] Create failing registration and longitudinal-history rendering tests for wound scoping, chronology, latest comparison, authoritative linked-detail outcomes, audit identities, inactive labels, measurement units, photos, escaped narratives, and legacy fallback in `tests/Feature/WoundAssessmentHistoryTest.php`
- [ ] T045 [P] [US4] Extend authorization regression coverage for registration details, daily review entry, scheduled review entry, and longitudinal patient history in `tests/Feature/RbacAuthorizationTest.php`: unauthorized users cannot read or mutate structured assessments, findings, treatments, audit identities, or photos, and tampered patient/registration/appointment/diagnosis identifiers are rejected without disclosing clinical data

### Implementation for User Story 4

- [ ] T046 [US4] Add eager-loaded structured assessment/finding/choice/treatment/audit/photo graphs and patient/registration scope enforcement in `app/Http/Controllers/RegistrationController.php` and `app/Http/Controllers/PatientMedicalRecordController.php`
- [ ] T047 [P] [US4] Build one escaped, reusable Arabic structured assessment summary with `mm` units, inactive historical labels, audit facts, photos, and `لم يُسجل` fallback in `resources/views/wound_assessments/partials/summary.blade.php`
- [ ] T048 [US4] Integrate structured baseline/follow-up summaries without removing legacy narrative details in `resources/views/reviews/partials/review_timeline.blade.php`, `resources/views/patients/partials/registration_record.blade.php`, and `resources/views/patients/partials/registration_wounds.blade.php`
- [ ] T049 [P] [US4] Add responsive comparison/timeline styles using existing design tokens in `resources/css/pages/registrations.css` and `resources/css/pages/patients.css`
- [ ] T050 [US4] Run `tests/Feature/WoundAssessmentHistoryTest.php`, `tests/Feature/RbacAuthorizationTest.php`, and affected cases in `tests/Feature/PatientMedicalRecordTest.php` and `tests/Feature/RegistrationDetailsTest.php`; fix US4 until all pass

**Checkpoint**: Authorized users can compare every wound over time without cross-wound or cross-registration leakage.

---

## Phase 7: User Story 5 — Maintain Approved Clinical Choices (Priority: P3)

**Goal**: Let administrators manage Arabic labels, ordering, and active state while stable keys and historical references remain unchanged.

**Independent Test**: Deactivate a used choice, prove it disappears from new forms and is rejected on manual submission, then prove its historical label still renders.

### Tests for User Story 5

- [ ] T051 [US5] Create failing authorization, immutable-key/category, label/order validation, activation lifecycle, new-form filtering, and historical-readability tests in `tests/Feature/AssessmentChoiceAdminTest.php`

### Implementation for User Story 5

- [ ] T052 [US5] Implement authorized list/update/toggle behavior that never deletes used choices or changes stable category/key in `app/Http/Controllers/Admin/AssessmentChoiceController.php`
- [ ] T053 [US5] Add admin-only assessment-choice routes using existing role middleware and named-route conventions in `routes/web.php`
- [ ] T054 [US5] Reuse the master-data layout for category filters, Arabic labels, order, and activation state in `resources/views/admin/master_data/assessment-choices.blade.php` and `resources/views/admin/master_data/partials/domain-nav.blade.php`
- [ ] T055 [P] [US5] Add assessment-choice modal/table interactions and styles in `resources/js/pages/admin.js` and `resources/css/pages/admin.css`
- [ ] T056 [US5] Run `tests/Feature/AssessmentChoiceAdminTest.php` and affected cases in `tests/Feature/MasterDataAdminTest.php`; fix US5 until all pass

**Checkpoint**: New assessments use only active approved choices while history preserves retired meanings.

---

## Phase 8: Polish and Cross-Cutting Validation

**Purpose**: Prove procedure behavior, preserve existing workflows, validate the source-field matrix, and finish quality gates.

- [ ] T057 Create MySQL-tagged integration coverage for procedure atomicity, ownership, category/activity enforcement, exact diagnosis coverage, idempotency, and scheduled uniqueness in `tests/Feature/MySqlStructuredWoundProcedureTest.php`
- [ ] T058 Update existing acceptance/review/photo fixtures to supply required structured payloads without weakening their original assertions in `tests/Feature/ClinicIntakeAndRegistrationTest.php`, `tests/Feature/DailyReviewTest.php`, `tests/Feature/ScheduledReviewTest.php`, `tests/Feature/WoundDiagnosisCreationTest.php`, and `tests/Feature/WoundPhotoDocumentationTest.php`
- [ ] T059 Verify every source row has an implemented owner, allowed value, input, and display matching the clinical-owner approval already recorded by T011 in `specs/012-structured-wound-assessment/contracts/hospital-form-field-matrix.md`
- [ ] T060 Run the focused and affected regression commands from `specs/012-structured-wound-assessment/quickstart.md` and resolve all failures
- [ ] T061 Run `vendor/bin/pint --dirty --format agent` for modified PHP and `npm run build` for production assets, then resolve formatting or build failures
- [ ] T062 Perform the desktop and narrow-screen Arabic RTL scenarios in `specs/012-structured-wound-assessment/quickstart.md` and confirm no wound identity, error, field, or audit fact becomes ambiguous
- [ ] T063 Run the representative-clinical-user protocol in `specs/012-structured-wound-assessment/quickstart.md` and record anonymized participant count, per-scenario completion result, timing, assistance, failure reason, and calculated pass rates in `specs/012-structured-wound-assessment/usability-evidence.md`; SC-003 and SC-007 require at least 90% success and SC-004 requires at least 90% completion under 8 minutes
- [ ] T064 Reconcile implementation against every FR/SC in `specs/012-structured-wound-assessment/spec.md`, mark all completed tasks in `specs/012-structured-wound-assessment/tasks.md`, and record any unmet acceptance evidence before completion

---

## Dependencies and Execution Order

### Phase Dependencies

- **Phase 1 — Setup**: Starts immediately.
- **Phase 2 — Foundation**: Depends on Phase 1 and blocks every user story.
- **US1 — Initial assessment**: Starts after Phase 2 and is the MVP.
- **US2 — Daily follow-up**: Starts after Phase 2; its latest-comparison UI can use seeded assessment data, but normal delivery follows US1.
- **US3 — Scheduled follow-up**: Depends on US2’s shared follow-up contract and completion procedure.
- **US4 — History**: Starts after Phase 2 with seeded data; full acceptance follows US1–US3 so all event sources are covered.
- **US5 — Choice administration**: Starts after Phase 2 independently of US1–US4.
- **Phase 8 — Polish**: Depends on every story included in the release.

### User Story Dependency Graph

```text
Setup → Foundation → US1 (MVP)
                   ├→ US2 → US3
                   ├→ US4 (full acceptance after US1–US3)
                   └→ US5
US1 + US2 + US3 + US4 + US5 → Polish
```

### Within Each User Story

1. Write the story tests and verify failure for missing behavior.
2. Implement validation and database procedure invariants.
3. Integrate the gateway/controller boundary.
4. Build the Blade/JavaScript/CSS workflow.
5. Run the narrow focused tests and complete the checkpoint.

### Parallel Opportunities

- T002 and T003 can run in parallel.
- T008 and T009 can run in parallel after the schema tasks are defined.
- T016–T018 can be written in parallel; T023, T026, and T027 touch separate files.
- T029 and T030 can be written in parallel; T034, T036, and T037 touch separate files.
- T045 can be written in parallel with T044; T047 and T049 can run in parallel after the history query shape is known.
- US5 can run alongside US1–US4 after Foundation.

## Parallel Examples

### User Story 1

```text
Task T016: Write initial acceptance behavior tests.
Task T017: Write complete added-wound behavior tests.
Task T018: Extend baseline-photo ownership tests.

After procedure/controller contracts stabilize:
Task T023: Build reusable initial fields.
Task T026: Implement request-page interactions.
Task T027: Style request and registration assessment cards.
```

### User Story 2

```text
Task T029: Write structured daily-review behavior tests.
Task T030: Extend review-photo ownership tests.

After controller data is available:
Task T034: Build structured follow-up fields.
Task T036: Implement review interactions.
Task T037: Style review cards and comparisons.
```

### User Story 4 and User Story 5

```text
Task T045: Extend authorization regression coverage.
Task T047: Build the structured history summary.
Task T049: Add responsive history styles.
Task T051: Write independent choice-administration tests.
```

## Implementation Strategy

### MVP First

1. Complete Setup and Foundation.
2. Complete US1, including complete added-wound behavior.
3. Stop and validate three isolated baselines, shared context, scheduling, photos, and audit identities.
4. Demonstrate the structured initial-registration workflow before adding follow-up behavior.

### Incremental Delivery

1. **US1** establishes reliable wound baselines.
2. **US2** adds comparable daily assessments for every wound.
3. **US3** gives scheduled appointments the same clinical contract.
4. **US4** exposes longitudinal comparison and legacy fallback.
5. **US5** enables controlled vocabulary maintenance.
6. Polish validates procedures, regressions, clinical labels, RTL, formatting, and assets.

## Notes

- Do not add or change dependencies.
- Use Artisan `make:` commands with `--no-interaction` for generated Laravel files.
- Do not edit deployed core migrations; all schema changes use new migrations.
- Keep database calls out of Blade templates and eager-load the complete graph.
- Procedure JSON contracts are MySQL executable specifications and require a manual PL/SQL rewrite in Phase 9.
- Clinical-owner approval of stable meanings and final Arabic labels must be recorded before T011 creates seed data; T059 verifies that implementation still matches that approved matrix.
