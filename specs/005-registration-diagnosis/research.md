# Technical Research & Decisions: Phase 5 — Registration & Diagnosis

**Feature Branch**: `005-registration-diagnosis`  
**Date**: 2026-09-26  

---

## Technical Decisions

### Decision 1: Controller Architecture & Route Separation
- **Context**: Phase 4 managed registration requests (`wc_request_reg_tb`) via `RegistrationRequestController`. Phase 5 manages actual active clinical registrations (`wc_registration_tb`) and wound diagnoses (`wc_reg_diagnosis_tb`).
- **Decision**: Create a dedicated `RegistrationController` (`app/Http/Controllers/RegistrationController.php`) to handle `/registrations` endpoints (`index`, `show`, `update`, `storeDiagnosis`, `discharge`).
- **Rationale**: Keeps controllers modular, adhering to Single Responsibility Principle, and cleanly separates referral request queue logic from clinical registration management.
- **Alternatives Considered**: Extending `RegistrationRequestController` (rejected due to overly bloated controller).

---

### Decision 2: Dual Audit Tracking (Actor vs Data-Entry Separation)
- **Context**: WCCMS constitution requires distinguishing the clinical employee who performed the exam/procedure from the authenticated user entering data.
- **Decision**:
  - `WC_RDIAG_CLINICAL_EMP_CD`: Selected from employee dropdown (defaults to logged-in user's linked employee profile if available).
  - `WC_RDIAG_ENTRY_USER_CD`: Automatically populated from `Auth::user()->PER_USER_CODE`.
  - `WC_REG_DISCHARGED_BY_EMP_CD`: Selected from employee dropdown on discharge modal.
- **Rationale**: Fully compliant with hospital governance and constitutional separation of duties.

---

### Decision 3: Blade Template Conversion & UI Component Reuse
- **Context**: Existing static pages `pre-views/registered-patients.html` and `pre-views/complete-registration.html` contain hand-built HTML/CSS for patient tables, wound forms, and modals.
- **Decision**:
  - `pre-views/registered-patients.html` → `resources/views/registrations/index.blade.php` (extends `layouts.app`).
  - `pre-views/complete-registration.html` → `resources/views/registrations/show.blade.php` (extends `layouts.app`).
  - Modularize modals into Blade partials (`resources/views/registrations/partials/wound_diagnosis_modal.blade.php` & `discharge_modal.blade.php`).
- **Rationale**: Leverages Phase 2 UI foundation, self-hosted Cairo Arabic typography, and shared Blade components.

---

### Decision 4: Form Request Validation & Model Security
- **Context**: Clinical data must be strictly validated before insertion into `wc_registration_tb` and `wc_reg_diagnosis_tb`.
- **Decision**: Create 3 dedicated Form Request classes:
  - `UpdateRegistrationRequest`: Validates `procedure_code` (nullable UUID existing in `wc_procedure_tb`) and `pmh_notes` (nullable text).
  - `StoreWoundDiagnosisRequest`: Validates `wound_type_code` (required UUID), `diagnosis_code` (required UUID), `dressing_code` (required UUID), `medication_code` (required UUID), and `clinical_nurse_code` (required UUID existing in `per_employee_tb`).
  - `DischargeRegistrationRequest`: Validates `discharging_nurse_code` (required UUID existing in `per_employee_tb`).
- **Rationale**: Enforces DB constraint safety and sanitizes input before touching Eloquent models.
