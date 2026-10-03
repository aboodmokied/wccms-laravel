# Feature Specification: Phase 5 — Registration & Diagnosis

**Feature Branch**: `005-registration-diagnosis`  
**Created**: 2026-09-26  
**Status**: Draft  
**Input**: User description: "Phase 5 — Registration & Diagnosis: View/edit registration records (department, date, procedure, PMH notes), add wound diagnoses (wound type, initial dressing, medication), patient discharge flow (manual by nurse or automatic), convert static pre-views pages into Blade templates."

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Registered Patients Listing & Filtering (Priority: P1) 🎯 MVP

As a clinic nurse or clinic supervisor, I want to view a list of all registered patients in the Wound Care Clinic and filter them by status (Active vs Discharged), National ID, name, or department, so that I can quickly access and review patient clinic records.

**Why this priority**: Core entry point to access clinical registrations in the Wound Care Clinic.

**Independent Test**: Login as a clinic nurse, access `/registrations`, toggle status filters (Active vs Discharged), and search by patient National ID to verify filtered results render accurately.

**Acceptance Scenarios**:

1. **Given** authenticated clinic nurse on `/registrations`, **When** the page loads, **Then** display all registrations with status `ACTIVE` by default, including patient name, National ID, department, registration date, and registering nurse.
2. **Given** clinic nurse filtering by `DISCHARGED` status, **When** clicking the discharged filter tab, **Then** list only registrations with status `DISCHARGED`, including discharge date and discharging employee.
3. **Given** clinic nurse typing a patient's National ID or name into the search field, **When** searching, **Then** filter the registered patients table in real-time or upon submission.

---

### User Story 2 - Registration Details & Clinical Procedure Management (Priority: P1) 🎯 MVP

As a clinic nurse, I want to open a patient's registration details page (`/registrations/{registration}`), view their department, registration date, associated initial procedure, and past medical history notes, and update procedure/PMH information when needed.

**Why this priority**: Vital clinical context required before assessing or diagnosing wounds.

**Independent Test**: Open a registration record, select an initial procedure from `WC_PROCEDURE_TB`, enter past medical history notes, submit updates, and verify data persists in `WC_REGISTRATION_TB`.

**Acceptance Scenarios**:

1. **Given** clinic nurse viewing `/registrations/{registration}`, **When** page loads, **Then** display patient demographics, current referral department (`WC_DEPARTMENT_TB`), registration date, selected procedure, and PMH notes.
2. **Given** clinic nurse updating initial procedure or PMH notes, **When** submitting the form, **Then** update `WC_REGISTRATION_TB` (`WC_REG_PROCEDURE_CD`, `WC_REG_PMH_NOTES`), recording the updating user.

---

### User Story 3 - Initial Wound Diagnosis & Treatment Plan Entry (Priority: P1) 🎯 MVP

As a clinic nurse, I want to add one or more wound diagnoses to a patient's registration record, selecting wound type, clinical diagnosis, initial dressing, and initial topical medication, so that each wound is documented with a baseline treatment plan.

**Why this priority**: Essential clinical capability to document wound care baseline for follow-up daily reviews.

**Independent Test**: Add a wound diagnosis record to an active registration with wound type, diagnosis, initial dressing, and medication; verify record is saved in `WC_REG_DIAGNOSIS_TB` with dual audit tracking (`WC_RDIAG_CLINICAL_EMP_CD` and `WC_RDIAG_ENTRY_USER_CD`).

**Acceptance Scenarios**:

1. **Given** clinic nurse on `/registrations/{registration}`, **When** adding a new wound diagnosis, **Then** select wound type (`WC_WOUND_TYPE_TB`), clinical diagnosis (`WC_DIAGNOSIS_TB`), initial dressing (`WC_DRESSING_TB`), initial medication (`WC_MEDICATION_TB`), and clinical nurse employee.
2. **Given** valid wound details submitted, **When** saved, **Then** create a record in `WC_REG_DIAGNOSIS_TB` storing `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_CLINICAL_EMP_CD`, and `WC_RDIAG_ENTRY_USER_CD`.
3. **Given** registration with existing wound diagnoses, **When** viewing the registration page, **Then** render all recorded wounds in a table with active options and option details.

---

### User Story 4 - Patient Discharge Workflow & Audit (Priority: P2)

As a clinic nurse, I want to discharge a patient from the Wound Care Clinic when treatment is complete or patient is transferred, updating registration status to `DISCHARGED` and recording discharge timestamp and discharging employee.

**Why this priority**: Completes the clinical lifecycle of a registration record.

**Independent Test**: Select an active registration, click "Discharge Patient", select discharging nurse, submit; verify status becomes `DISCHARGED` and `WC_REG_DISCHARGED_AT` / `WC_REG_DISCHARGED_BY_EMP_CD` are populated.

**Acceptance Scenarios**:

1. **Given** active registration (`WC_REG_STATUS = 'ACTIVE'`), **When** clinic nurse submits discharge action, **Then** set `WC_REG_STATUS = 'DISCHARGED'`, `WC_REG_DISCHARGED_AT = NOW()`, and `WC_REG_DISCHARGED_BY_EMP_CD`.
2. **Given** registration already `DISCHARGED`, **When** viewing registration page, **Then** display discharge badge, timestamp, discharging nurse, and disable new wound diagnosis additions.

---

### Edge Cases

- **Duplicate Wound Diagnoses**: What happens if a nurse submits the exact same wound type and diagnosis twice for the same registration? The system allows multiple wounds of the same type if located in different body sites, identified by separate diagnosis records.
- **Discharging Already Discharged Patient**: What happens if a user attempts to discharge a patient who is already discharged? The system blocks double discharge with an Arabic validation error.
- **Discharge with No Wound Diagnoses**: What happens if a nurse attempts to discharge a registration before any wound diagnosis has been documented? System prompts confirmation warning.
- **Actor vs Data-Entry Audit**: If a data-entry user enters a diagnosis on behalf of a different clinical nurse, `WC_RDIAG_CLINICAL_EMP_CD` records the nurse who performed the exam while `WC_RDIAG_ENTRY_USER_CD` records the logged-in system user.

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST display a list of registered patients (`/registrations`) with filter tabs for `ACTIVE` and `DISCHARGED` status and real-time patient search by National ID/name.
- **FR-002**: System MUST render registration details page (`/registrations/{registration}`) using Blade template adapted from `pre-views/complete-registration.html` and `pre-views/registered-patients.html`.
- **FR-003**: System MUST allow updating registration clinical attributes: initial procedure (`WC_REG_PROCEDURE_CD`) from active `WC_PROCEDURE_TB` options and PMH notes (`WC_REG_PMH_NOTES`).
- **FR-004**: System MUST allow adding multiple wound diagnosis records (`WC_REG_DIAGNOSIS_TB`) per registration.
- **FR-005**: System MUST require selecting active wound type (`WC_WOUND_TYPE_TB`), clinical diagnosis (`WC_DIAGNOSIS_TB`), initial dressing (`WC_DRESSING_TB`), initial medication (`WC_MEDICATION_TB`), and clinical nurse (`PER_EMPLOYEE_TB`) when adding a wound diagnosis.
- **FR-006**: System MUST record the logged-in user code (`PER_USER_CODE`) in `WC_RDIAG_ENTRY_USER_CD` and clinical nurse code (`PER_EMP_CODE`) in `WC_RDIAG_CLINICAL_EMP_CD` for audit separation.
- **FR-007**: System MUST provide a discharge action for `ACTIVE` registrations, setting status to `DISCHARGED`, current datetime to `WC_REG_DISCHARGED_AT`, and discharging nurse code to `WC_REG_DISCHARGED_BY_EMP_CD`.
- **FR-008**: System MUST prevent modification of wound diagnoses on registrations with status `DISCHARGED`.
- **FR-009**: System MUST enforce authorization policies ensuring only authenticated clinic staff (`NURSE` or `ADMIN` roles) can view or modify registration records.
- **FR-010**: All UI components and user messages MUST render in Arabic with native RTL layout.

---

### Key Entities

- **Registration (`WC_REGISTRATION_TB`)**: Main clinical registration record created upon referral acceptance. Attributes: `WC_REG_CODE` (PK), `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD`, `WC_REG_PROCEDURE_CD`, `WC_REG_PMH_NOTES`, `WC_REG_DATE`, `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, `WC_REG_STATUS` (`ACTIVE`, `DISCHARGED`), `WC_REG_DISCHARGED_AT`, `WC_REG_DISCHARGED_BY_EMP_CD`.
- **Registration Diagnosis / Wound (`WC_REG_DIAGNOSIS_TB`)**: Individual wound diagnosis associated with a registration. Attributes: `WC_RDIAG_CODE` (PK), `WC_RDIAG_REGISTRATION_CD`, `WC_RDIAG_DIAGNOSIS_CD`, `WC_RDIAG_WOUND_TYPE_CD`, `WC_RDIAG_CLINICAL_EMP_CD`, `WC_RDIAG_ENTRY_USER_CD`.
- **Master Reference Models**: `WC_WOUND_TYPE_TB`, `WC_DIAGNOSIS_TB`, `WC_DRESSING_TB`, `WC_MEDICATION_TB`, `WC_PROCEDURE_TB`, `WC_PMH_TB`, `WC_DEPARTMENT_TB`.
- **Employee (`PER_EMPLOYEE_TB`)**: Staff directory entity representing clinical nurses and doctors.

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Clinic nurses can view active registered patients and filter by status in under 2 seconds.
- **SC-002**: Clinic nurses can complete initial registration details and document initial wound diagnoses in under 3 minutes per patient.
- **SC-003**: 100% of recorded wound diagnoses accurately maintain dual audit tracking (clinical employee vs data-entry user).
- **SC-004**: 100% of discharge operations correctly transition registration status to `DISCHARGED` with recorded timestamp and nurse ID.

---

## Assumptions

- Registration records are initially generated by referral request acceptance (Phase 4 `sp_accept_registration_request`).
- Reference master data (departments, wound types, diagnoses, dressings, medications, procedures) are pre-populated via Phase 1 seeders.
- Localized Arabic dictionary keys exist under `lang/ar/` from Phase 2 UI foundation.
