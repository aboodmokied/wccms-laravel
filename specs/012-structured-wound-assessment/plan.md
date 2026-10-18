# Implementation Plan: Phase 5B — Structured Wound Assessment & Hospital Form Parity

**Branch**: `012-structured-wound-assessment` | **Date**: 2026-10-12 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/012-structured-wound-assessment/spec.md`

## Summary

Extend the existing registration and shared daily/scheduled review workflows so every registration diagnosis owns a complete structured baseline and subsequent comparable wound assessments. The design adds a canonical assessment table, category-qualified clinical choices, multi-select findings, registration-level allergy/PMH/follow-up context, assessment-linked photos, and idempotent visit completion. Laravel validates HTTP input and renders Arabic RTL interfaces; database stored procedures atomically own request acceptance, post-registration wound creation, and daily/scheduled review completion. Existing review details and legacy records remain readable without inferred structured data.

## Technical Context

**Language/Version**: PHP 8.3; JavaScript ES modules; Blade/HTML5

**Primary Dependencies**: Laravel Framework 13.32, Eloquent, Blade, Vite 8, Tailwind CSS 4, Bootstrap RTL 5.3.3, Bootstrap Icons 1.11.3

**Storage**: MySQL for Phases 1–8 with Oracle port planned for Phase 9; relational DB-first schema, MySQL procedures/views, and Laravel public-disk wound images

**Testing**: PHPUnit 12.5.35; Laravel HTTP/database/storage testing; in-memory SQLite main suite with a bound procedure-gateway test implementation; tagged MySQL procedure integration tests

**Target Platform**: Authenticated server-rendered web application on desktop and narrow-screen browsers, Arabic RTL first

**Project Type**: Laravel monolith with server-rendered Blade UI

**Performance Goals**: Three-wound follow-up can be completed in under 8 minutes by at least 90% of representative users; latest wound status, dimensions, treatment, and photo can be located within 30 seconds; wound history queries eager-load the complete assessment graph without per-row queries

**Constraints**: Database-owned core clinical mutations; UUID DB-first names; actor/data-entry separation; no dependency changes; no inference or destructive backfill of legacy clinical text; daily and scheduled clinical fields must remain identical; exact Arabic vocabulary requires clinical-owner approval

**Scale/Scope**: Four new clinical tables, nullable extensions to three populated tables, three aggregate procedure contracts, one procedure gateway, five affected workflow/history controllers, shared initial/follow-up Blade components, one assessment-choice admin domain, and focused plus regression PHPUnit coverage

## Constitution Check

*GATE: Passed before research and passed again after design.*

| Principle / gate | Design response | Status |
|---|---|---|
| Laravel + database stack | Uses the installed Laravel 13/PHP 8.3 application and local MySQL schema; Oracle-specific work remains Phase 9. | PASS |
| Database-first core workflows | Request acceptance, complete wound addition, and daily/scheduled completion call aggregate MySQL stored procedures through a gateway. Controllers do not orchestrate clinical row writes. | PASS |
| Unified naming | New tables use `WC_*_TB`; columns use entity prefixes, UUID `*_CODE` primary keys, and `*_CD` foreign keys. | PASS |
| Arabic and RTL | Forms, validation, choice labels, empty states, history, and responsive layouts are Arabic RTL. | PASS |
| Clinical actor vs entry user | Every assessment stores `PER_EMPLOYEE_TB` clinical identities separately from `PER_USERS_TB` entry identity and completion time. | PASS |
| MySQL first, Oracle later | Fluent migrations define portable tables/columns/constraints; raw MySQL is limited to procedures/views that Phase 9 will rewrite in PL/SQL. | PASS |
| Quality gates | PHPUnit covers HTTP behavior and mocked/real procedure contracts; modified PHP is formatted with Pint. | PASS |

Post-design review: The relational model, procedure contracts, and interface contracts preserve all seven gates. No constitutional exception is required.

## Design Artifacts

- [research.md](research.md): architecture, compatibility, procedure, idempotency, UI, and testing decisions.
- [data-model.md](data-model.md): tables, columns, relationships, invariants, and lifecycle.
- [contracts/web-workflows.md](contracts/web-workflows.md): route payloads, validation, outcomes, and display contracts.
- [contracts/hospital-form-field-matrix.md](contracts/hospital-form-field-matrix.md): source-form ownership, allowed values, entry location, and display location.
- [quickstart.md](quickstart.md): automated and manual acceptance validation.

## Database Design and Migration Strategy

1. Create `WC_ASSESSMENT_CHOICE_TB`, `WC_WOUND_ASSESSMENT_TB`, `WC_ASSESSMENT_FINDING_TB`, and `WC_REGISTRATION_PMH_TB` using fluent schema migrations.
2. Add nullable legacy-safe columns to `WC_REGISTRATION_TB`, `WC_DAILY_REVIEW_TB`, and `WC_WOUND_PHOTO_TB`. New procedure paths require them where the specification requires; historical rows remain null and readable.
3. Add indexes for choice lookup, assessment chronology, review linkage, registration PMH uniqueness, idempotency, and one visit per scheduled appointment.
4. Record clinical-owner approval of the stable meanings and final Arabic labels in the field matrix before seeding assessment choices. Seed deterministic category/key pairs only after that approval. Deactivation is the retirement mechanism; used rows are never deleted.
5. Install three MySQL procedure contracts:
   - `SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS`: accepts the shared registration context and all complete baseline assessments atomically.
   - `SP_WC_ADD_WOUND_WITH_ASSESSMENT`: adds a diagnosis to an active registration only with its complete baseline and audit data.
   - `SP_WC_COMPLETE_REVIEW_WITH_ASSESSMENTS`: completes daily or scheduled visits with exact diagnosis coverage, structured findings, legacy detail snapshots, shared follow-up planning, photos metadata, and appointment transition.
6. Add a read view or equivalent indexed Eloquent query contract for chronological assessment history without mutating legacy rows.
7. Persist every validated photo to its immutable final UUID-based path before invoking the procedure, then include that path in the aggregate payload. A storage failure prevents the procedure call; a procedure failure removes the newly written files. No file rename or other required storage operation occurs after the database commits, so successful photo metadata never depends on a later finalization step.

## Application Design

### Models and query boundaries

Add DB-first models for assessment choices, wound assessments, assessment findings, and registration PMH links. Extend existing registration, diagnosis, review detail, and photo relationships with explicit keys and return types. Controllers eager-load choice/findings/treatment/audit/photo relationships before rendering; Blade templates never issue queries.

### Clinical workflow gateway

Add a `ClinicalWorkflowGatewayInterface` with aggregate methods for request acceptance, complete wound addition, and review completion. The MySQL implementation serializes validated payloads and calls the procedures. Bind a deterministic SQLite implementation only in the testing environment so HTTP tests exercise the same contract; tagged MySQL tests verify the real procedures, atomicity, ownership, and duplicate protection.

### Request validation

Expand `AcceptRegistrationRequest`, `StoreWoundDiagnosisRequest`, and `StoreDailyReviewRequest` with nested Arabic attributes and after-validation invariants:

- complete per-diagnosis required fields;
- unique diagnosis identities and exact review coverage;
- category-qualified active choices;
- measurement ranges and precision;
- dry/exudate consistency;
- allergy description and PMH “None” exclusivity;
- location/notes consistency;
- treatment presence;
- shared follow-up date/frequency/objective consistency;
- scheduled route ownership and eligible state;
- valid photo arrays.

The database procedures repeat ownership and clinical consistency checks at the mutation boundary.

### Initial assessment UX

Upgrade the pending-request acceptance screen into a shared-context section followed by repeated wound cards. Each card groups identity, wound state, tissue/margin, exudate/odor/pain, optional photos, measurements/location, and treatment. A sticky summary shows incomplete wound cards and takes the user to the first error. Adding a diagnosis after registration uses the same complete assessment component instead of the current minimal modal.

### Daily and scheduled follow-up UX

Keep one `daily_create` workflow for both visit sources. Every diagnosis card is mandatory and shows a compact latest-assessment comparison above the new fields. Follow-up captures the hospital daily set, optional comparable measurements/margin findings, treatment, narratives, outcome, and photos. `WC_REVIEW_DETAIL_TB.WC_DREVD_STATUS` remains the single authoritative outcome value and the linked canonical assessment does not duplicate it. Shared clinician/audit and next-plan controls appear once. Old input and wound-qualified Arabic errors remain visible after failed submission.

### History and comparison UX

Registration and longitudinal patient views reuse one structured assessment summary partial. Each wound timeline shows baseline, daily, and scheduled events in chronological order with choice labels, measurements, treatment, the authoritative linked-detail outcome, photos, clinical participants, entry user, and timestamp. Legacy detail rows continue to show their narrative/treatment/outcome/photo facts and render absent structured fields as `لم يُسجل`.

### Assessment-choice administration

Reuse the established admin master-data patterns for listing, editing Arabic labels/order, and toggling active state. Category and stable key become immutable once created. Only active choices appear on new forms; inactive labels remain eager-loaded for historical display.

## Testing Strategy

1. Write feature tests before implementation for complete initial acceptance, incomplete diagnosis rejection, allergy/PMH conflicts, measurement boundaries, multi-select findings, audit separation, and diagnosis/photo isolation.
2. Cover daily and scheduled parity, exact diagnosis coverage, cross-registration rejection, ineligible appointment states, dry/exudate conflict, legacy detail snapshots, and idempotent retries.
3. Cover history rendering for baseline/follow-up chronology, authoritative linked-detail outcomes, inactive labels, audit identities, photos, and legacy `لم يُسجل` fallback.
4. Cover existing role authorization across registration, daily review, scheduled review, and longitudinal-history routes for every expanded assessment relationship.
5. Cover storage failure before a procedure call and cleanup of final-path files when the procedure rejects the aggregate; no test may rely on a post-commit file rename.
6. Cover admin choice activation/deactivation and wrong-category submission.
7. Add MySQL-tagged procedure integration tests for aggregate atomicity and database-owned invariants; use the gateway test implementation for the default SQLite HTTP suite.
8. Run the focused suite after each workflow, then affected regression tests, Pint, the production Vite build, and the representative-user acceptance protocol in `quickstart.md`.

## Project Structure

### Documentation

```text
specs/012-structured-wound-assessment/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── usability-evidence.md (created during acceptance validation)
├── contracts/
│   ├── hospital-form-field-matrix.md
│   └── web-workflows.md
└── tasks.md
```

### Source Code

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/AssessmentChoiceController.php
│   │   ├── DailyReviewController.php
│   │   ├── PatientMedicalRecordController.php
│   │   ├── RegistrationController.php
│   │   └── RegistrationRequestController.php
│   └── Requests/
│       ├── AcceptRegistrationRequest.php
│       ├── StoreDailyReviewRequest.php
│       └── StoreWoundDiagnosisRequest.php
├── Models/
│   ├── WcAssessmentChoice.php
│   ├── WcAssessmentFinding.php
│   ├── WcRegistrationPmh.php
│   ├── WcWoundAssessment.php
│   └── existing registration/review/photo models
└── Services/ClinicalWorkflows/
    ├── ClinicalWorkflowGatewayInterface.php
    ├── MySqlClinicalWorkflowGateway.php
    └── SqliteClinicalWorkflowGateway.php

database/
├── migrations/
│   ├── create_structured_wound_assessment_tables.php
│   ├── add_structured_context_to_wound_workflows.php
│   └── install_structured_wound_workflow_procedures.php
└── seeders/WcAssessmentChoiceSeeder.php

resources/
├── views/
│   ├── admin/master_data/
│   ├── requests/show.blade.php
│   ├── reviews/daily_create.blade.php
│   ├── reviews/partials/review_timeline.blade.php
│   ├── registrations/partials/wound_diagnosis_modal.blade.php
│   ├── patients/partials/registration_record.blade.php
│   ├── patients/partials/registration_wounds.blade.php
│   └── wound_assessments/partials/
│       ├── fields.blade.php
│       ├── latest-comparison.blade.php
│       └── summary.blade.php
├── css/pages/
│   ├── admin.css
│   ├── patients.css
│   ├── registrations.css
│   ├── requests.css
│   └── reviews.css
└── js/pages/
    ├── admin.js
    ├── requests.js
    └── reviews.js

tests/Feature/
├── AssessmentChoiceAdminTest.php
├── MySqlStructuredWoundProcedureTest.php
├── StructuredDailyReviewTest.php
├── StructuredInitialAssessmentTest.php
├── StructuredScheduledReviewTest.php
├── WoundAssessmentHistoryTest.php
└── affected existing regression tests
```

**Structure Decision**: Extend the existing Laravel monolith and its DB-first models, controllers, Blade views, page assets, and PHPUnit conventions. No new top-level application folder or package is introduced.

## Complexity Tracking

No constitution violations require justification.
