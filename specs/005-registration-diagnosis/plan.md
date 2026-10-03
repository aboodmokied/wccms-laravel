# Implementation Plan: Phase 5 — Registration & Diagnosis

**Branch**: `005-registration-diagnosis` | **Date**: 2026-09-26 | **Spec**: [specs/005-registration-diagnosis/spec.md](file:///e:/wamp64/www/wccms/specs/005-registration-diagnosis/spec.md)

**Input**: Feature specification from `/specs/005-registration-diagnosis/spec.md`

---

## Summary

Phase 5 implements the clinical registration and initial wound diagnosis module for the Wound Care Clinic Management System (WCCMS). Building upon the database-first architecture and referral request acceptance (Phase 4), this phase provides:
1. Active and Discharged registrations listing with real-time search & status filtering (`/registrations`).
2. Registration details management page (`/registrations/{registration}`) for procedure selection (`WC_REG_PROCEDURE_CD`) and PMH notes (`WC_REG_PMH_NOTES`).
3. Initial wound diagnosis entry modal/form creating `WC_REG_DIAGNOSIS_TB` records linking wound type, diagnosis, initial dressing, and initial medication with dual actor/data-entry audit (`WC_RDIAG_CLINICAL_EMP_CD` vs `WC_RDIAG_ENTRY_USER_CD`).
4. Patient discharge workflow updating `WC_REGISTRATION_TB` status to `DISCHARGED` with timestamp and discharging employee tracking.
5. Conversion of static templates `pre-views/registered-patients.html` and `pre-views/complete-registration.html` into integrated Blade views.

---

## Technical Context

**Language/Version**: PHP 8.3 (Laravel 11.x)  
**Primary Dependencies**: `laravel/framework`, `firebase/php-jwt`, Vite asset pipeline  
**Storage**: MySQL `wccms` DB-First tables (`wc_registration_tb`, `wc_reg_diagnosis_tb`, `wc_wound_type_tb`, `wc_diagnosis_tb`, `wc_dressing_tb`, `wc_medication_tb`, `wc_procedure_tb`, `wc_pmh_tb`, `per_employee_tb`, `per_users_tb`)  
**Testing**: PHPUnit / Laravel Feature & Unit test runner (`php artisan test`)  
**Target Platform**: Web (Modern desktop browsers with Arabic RTL layout)  
**Project Type**: Laravel Web Application  
**Performance Goals**: Page render under 500ms, search/filter under 200ms  
**Constraints**: 100% Arabic RTL layout, strict DB-First schema preservation (no schema migrations required; tables pre-exist), dual audit tracking (clinical employee vs data-entry user)  
**Scale/Scope**: Clinical staff users (Nurses, Admins), hundreds of daily active registrations  

---

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Database-First Architecture**: ✅ PASS. Utilizes pre-existing `wc_registration_tb` and `wc_reg_diagnosis_tb` tables. Models map directly to `_tb` names, CHAR(36) UUID PKs, no `$timestamps`.
- **Naming Protocol**: ✅ PASS. Tables use `<PREFIX>_<name>_TB` and columns use `<PREFIX>_` conventions.
- **Arabic/RTL Support**: ✅ PASS. UI Blade views enforce `dir="rtl"`, Cairo Arabic typography, and `lang/ar/` dictionaries.
- **Actor vs. Data-Entry Separation**: ✅ PASS. Explicitly distinguishes `WC_RDIAG_CLINICAL_EMP_CD` (examining nurse) from `WC_RDIAG_ENTRY_USER_CD` (authenticated user), and `WC_REG_DISCHARGED_BY_EMP_CD` (discharging nurse).

---

## Project Structure

### Documentation (this feature)

```text
specs/005-registration-diagnosis/
├── spec.md              # Feature specification
├── plan.md              # Implementation plan (/speckit-plan output)
├── research.md          # Research & technical decisions
├── data-model.md        # DB-First entity mapping & relationships
├── quickstart.md        # Runnable verification guide
├── contracts/           # Route & controller API contracts
│   └── registration-api.md
└── checklists/
    └── requirements.md  # Specification quality checklist
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── RegistrationController.php
│   └── Requests/
│       ├── UpdateRegistrationRequest.php
│       ├── StoreWoundDiagnosisRequest.php
│       └── DischargeRegistrationRequest.php
└── Models/
    ├── WcRegistration.php
    ├── WcRegDiagnosis.php
    ├── WcWoundType.php
    ├── WcDiagnosis.php
    ├── WcDressing.php
    ├── WcMedication.php
    └── WcProcedure.php

resources/views/
└── registrations/
    ├── index.blade.php
    ├── show.blade.php
    └── partials/
        ├── registration_info.blade.php
        ├── wound_diagnosis_modal.blade.php
        └── discharge_modal.blade.php

routes/
└── web.php

tests/Feature/
├── RegistrationListingTest.php
├── RegistrationDetailsTest.php
├── WoundDiagnosisCreationTest.php
└── RegistrationDischargeTest.php
```

**Structure Decision**: Standard single-project Laravel structure integrating controllers, form requests, Blade views, and PHPUnit feature tests.

---

## Complexity Tracking

*No constitution violations. All structures align with pre-existing DB-First architecture.*
