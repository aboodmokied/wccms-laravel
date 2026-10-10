# Implementation Plan: Longitudinal Patient Medical Record

**Branch**: `011-longitudinal-patient-record` | **Date**: 2026-10-08 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/011-longitudinal-patient-record/spec.md`

## Summary

Turn the existing patient page into a read-only longitudinal medical record that presents every registration as a clearly bounded episode of care. Active registrations appear first, historical registrations follow in deterministic reverse chronology, and one registration is expanded initially. Each episode includes registration context, wounds and baseline photos, scheduled appointments, reviews, review-owned photos, and distinct clinical and data-entry identities. The implementation reuses the existing authenticated patient and registration routes, uses constrained eager loading to avoid N+1 queries, and replaces the horizontal history slider with accessible RTL `<details>` sections.

## Technical Context

**Language/Version**: PHP 8.3, JavaScript ES modules  
**Primary Dependencies**: Laravel Framework 13.32, Blade, Bootstrap 5.3.3 RTL, Vite 8, Laravel Vite Plugin 3.1  
**Storage**: MySQL for current development; Oracle remains a later target; wound images use the existing filesystem-backed authenticated photo route  
**Testing**: PHPUnit 12.5 through `php artisan test --compact`; Laravel Pint for PHP formatting; Vite production build for frontend assets  
**Target Platform**: Authenticated Laravel web application on modern desktop, tablet, and mobile browsers  
**Project Type**: Server-rendered web application  
**Performance Goals**: Initial page content usable in under two seconds with up to 10 registrations and 200 reviews; at least 20 registrations remain discoverable  
**Constraints**: Arabic RTL; read-only aggregate; deterministic ordering; no cross-registration data mixing; no duplicated clinical item or image; clinic-wide `hospital.jwt` authorization remains unchanged; portable SQL suitable for the later Oracle target  
**Scale/Scope**: One patient, up to 20 registration episodes in the usability scenario, up to 200 reviews in the performance scenario, including appointments, wound assessments, and photos

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-checked after Phase 1 design.*

### Pre-design gate

| Principle | Result | Evidence |
|---|---|---|
| Laravel/PHP and Oracle target | PASS | Uses existing Laravel controllers, Eloquent relationships, Blade, and portable ordering expressions. |
| Database-first business rules | PASS | This feature only reads and presents existing clinical data; it introduces no mutation or duplicated business rule. |
| Naming protocol | PASS | Existing `WC_*` schema and model mappings remain unchanged. |
| Arabic and RTL | PASS | User-facing structure and labels remain Arabic and use the existing RTL layout. |
| Actor and data-entry separation | PASS | Clinical employee and entry user are loaded and displayed as distinct identities. |
| MySQL first, Oracle later | PASS | No vendor-specific schema or query feature is introduced. |

### Post-design gate

PASS. The data model adds no persisted entity, the web contract adds no mutation, and the selected eager-loading and deterministic ordering strategy remains within the constitution. No justified exception is required.

## Project Structure

### Documentation (this feature)

```text
specs/011-longitudinal-patient-record/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── patient-medical-record-ui.md
├── checklists/
│   └── requirements.md
└── spec.md
```

### Source Code (repository root)

```text
app/
└── Http/Controllers/
    └── PatientMedicalRecordController.php

resources/
├── css/pages/
│   └── patients.css
└── views/
    ├── patients/
    │   ├── show.blade.php
    │   └── partials/
    │       ├── patient_demographics_banner.blade.php
    │       ├── registration_record.blade.php
    │       ├── registration_wounds.blade.php
    │       └── registration_appointments.blade.php
    └── reviews/partials/
        └── review_timeline.blade.php

tests/
└── Feature/
    └── PatientMedicalRecordTest.php

vite.config.js
```

**Structure Decision**: Keep the existing monolithic Laravel application structure. The patient page owns the outer longitudinal composition, while reusable Blade partials retain registration boundaries. No new application layer, API module, or top-level directory is needed.

## Phase 0: Research Outcome

Research decisions are recorded in [research.md](./research.md). The chosen design uses one authenticated server-rendered request with a bounded, constrained eager-load graph. All registration summaries and details are available without JavaScript, while native collapsed sections and lazy image loading control visual and network cost. The target scale does not justify a new fragment endpoint, cursor pagination, caching layer, or schema migration before representative measurements show a need.

## Phase 1: Design Outcome

- [data-model.md](./data-model.md) defines the existing entities, ownership boundaries, display projections, invariants, and stable ordering.
- [patient-medical-record-ui.md](./contracts/patient-medical-record-ui.md) defines the authenticated web and rendered-UI contract.
- [quickstart.md](./quickstart.md) defines focused automated checks and manual acceptance scenarios.
- The patient page keeps `patients.show`; each episode links to `registrations.show` for focused workflows and permitted actions.
- Baseline photos render once with their wound. A review renders only its own follow-up photos and can reference the baseline without duplicating it.

## Implementation Strategy

1. Update `PatientMedicalRecordController::show` to load the complete read model with constrained eager loading and stable date-plus-code ordering. Include procedure, audit identities, appointments, baseline photos, review details, and review-owned photos. Do not query from Blade or call lazy accessors.
2. Derive the initially open registration after ordering: the newest active episode, or the newest historical episode when none is active.
3. Replace the patient page slider with registration-scoped native `<details>` sections. Keep demographics once at patient level and expose each episode's status, date, department, nurse, procedure, discharge context, counts, and dedicated-record link in its summary.
4. Extract registration wounds and appointments into focused Blade partials. Parameterize the existing review timeline so repeated instances have unique IDs, omit mutating calls to action in the aggregate view, and do not repeat baseline photos.
5. Add `patients.css` to Vite inputs and move patient-history presentation out of inline CSS and JavaScript. Reuse the established Bootstrap RTL and clinical component styles.
6. Add focused feature coverage for ordering, episode isolation, audit identities, appointments, photos, empty states, direct links, authentication, and bounded query growth.

## Data Retrieval Boundary

The controller loads registrations for the requested patient only. Each nested relation remains reachable through its owning registration:

```text
Patient
└── Registrations
    ├── Department, procedure, nurse, entry user, discharging nurse
    ├── Diagnoses/wounds
    │   ├── Diagnosis, wound type, clinical employee, entry user
    │   └── Initial photos only, with nurse and entry user
    ├── Scheduled reviews, with creator
    └── Daily reviews
        ├── Nurse, entry user, scheduled-review link
        └── Details
            ├── Registration diagnosis, dressing, medication
            └── Review photos only, with nurse and entry user
```

Registration, review, appointment, detail, and photo collections receive stable code tie-breakers. Baseline photos are excluded from review-photo collections, and review-linked photos are excluded from the baseline collection. The implementation must retain all primary and foreign keys needed by Eloquent when selecting constrained columns.

## Performance and Measurement

- Use eager loading so query count remains effectively constant as registrations and reviews grow.
- Use `withCount` only where counts are needed without materializing a second copy of a relation.
- Lazy-load image binaries with `loading="lazy"`; serve them through the existing authenticated route.
- Avoid database queries in Blade and avoid `WcRegDiagnosis::latest_detail` on this page because it may lazy-load per wound.
- Validate query growth with representative small and 10-registration/200-review fixtures. Measure the rendered page against the two-second success criterion in the target environment.
- Existing foreign-key indexes are sufficient initially. Add composite ordering indexes only after timings and `EXPLAIN` demonstrate a failure; any such index is a separate measured migration.

## Complexity Tracking

No constitution violation or exceptional complexity is introduced. The design avoids a new JSON/fragment endpoint, custom client-side accordion state, caching invalidation, and unmeasured schema optimization.
