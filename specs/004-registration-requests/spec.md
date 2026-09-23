# Feature Specification: Phase 4 — Registration Requests

**Feature Branch**: `004-registration-requests`
**Created**: 2026-09-23
**Status**: Draft
**Input**: User description: "Phase 4 — Registration Requests , without any DB changies"

## Overview

Phase 4 implements the Hospital Ward Referral and Wound Care Clinic Registration Request workflow in WCCMS without modifying the existing database schema.

In this workflow, a hospital ward nurse searches for a patient in the hospital patient master index, reviews their current admission details (department, attending doctor, admission date), and submits a referral request (`wc_request_reg_tb`). The request is assigned status `PENDING` and placed in the Wound Care Clinic incoming referral queue.

A Wound Care Clinic nurse opens the request, visits the patient in their ward, and updates the status to `VISIT_IN_PROGRESS`. Following the clinical assessment, the clinic nurse marks the request as `ACCEPTED` (which triggers in-database creation of an `ACTIVE` clinical registration in `wc_registration_tb`) or `NOT_ELIGIBLE` with documented clinical justification.

All user interfaces, queues, forms, and notifications are rendered in Arabic with full Right-to-Left (RTL) layout support.

## Clarifications

### Session 2026-09-23
- Q: When a clinic nurse accepts a referral (`ACCEPTED`), how should the clinical registration's department (`WC_REG_DEPARTMENT_CD`) be determined? → A: Assign the default Wound Care Clinic department code (`WC_DEPT_CODE`), retaining the referring ward in referral snapshot context.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Ward Referral & Patient Search (Priority: P1)

As a hospital ward nurse, I want to search for a patient by National ID or name, view their hospital admission details, and submit a referral request to the Wound Care Clinic so that the patient can be evaluated for specialized wound care.

**Why this priority**: Referral submission initiates the patient intake lifecycle. Without referral requests, clinic nurses cannot conduct ward visits or create clinical registrations.

**Independent Test**: Can be tested independently by searching for a patient by National ID, filling out the referral request form with referral reason, selecting the attending ward nurse, and confirming a `PENDING` request record is created in `wc_request_reg_tb` with patient snapshot attributes.

**Acceptance Scenarios**:

1. **Given** a ward nurse searching for a patient by National ID (`MR_PAT_ID_NO`) or Full Name (`MR_PAT_FULL_NAME`), **When** matching records are found, **Then** the system displays the patient's demographics and current admission details (ward/department, attending doctor, admission date).
2. **Given** a patient not found in the local database, **When** the nurse triggers "Sync from Hospital System", **Then** the system retrieves patient data from the external hospital patient index, creates the local record in `mr_patient_tb`, and populates the referral form.
3. **Given** a referral submission form, **When** the ward nurse enters referral reason/notes and submits, **Then** the system creates a `wc_request_reg_tb` record with status `PENDING`, sets `WC_REQ_NURSE_CD` to the selected ward employee (`per_employee_tb`), sets `WC_REQ_ENTRY_USER_CD` to the logged-in user (`per_users_tb`), populates snapshot fields (`WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`), and displays a success notification in Arabic ("تم تقديم طلب التحويل للعيادة بنجاح").
4. **Given** a patient with an existing `PENDING` request or an `ACTIVE` clinic registration (`wc_registration_tb`), **When** a nurse attempts to submit a new referral, **Then** the system blocks submission and displays an informative warning message in Arabic.

---

### User Story 2 - Clinic Incoming Queue & Ward Visit Intake (Priority: P1)

As a Wound Care Clinic nurse, I want to view all incoming `PENDING` referral requests, claim a request to perform a ward visit (`VISIT_IN_PROGRESS`), and either accept the patient for clinic registration or declare them not eligible so that clinic admission decisions are properly assessed and documented.

**Why this priority**: Governs clinic intake decision-making and completes the referral lifecycle.

**Independent Test**: Can be tested independently by logging in as a clinic nurse, selecting a `PENDING` request, transitioning it to `VISIT_IN_PROGRESS`, and completing intake by selecting `ACCEPTED` or `NOT_ELIGIBLE`.

**Acceptance Scenarios**:

1. **Given** a clinic nurse viewing the Incoming Requests Queue, **When** pending referrals exist, **Then** the system presents a sorted table showing patient snapshot details, referring ward, requesting nurse, submission time, and referral reason in Arabic RTL layout.
2. **Given** a `PENDING` referral request, **When** the clinic nurse initiates a ward visit, **Then** the system updates `WC_REQ_STATUS` to `VISIT_IN_PROGRESS` and sets `WC_REQ_REVIEWED_BY_CD` to the clinic nurse's employee code (`per_employee_tb`).
3. **Given** a request in `VISIT_IN_PROGRESS`, **When** the clinic nurse accepts the patient, **Then** the system updates `WC_REQ_STATUS` to `ACCEPTED`, which invokes the database logic to automatically create an `ACTIVE` record in `wc_registration_tb` with `WC_REG_DEPARTMENT_CD` set to the default Wound Care Clinic department code.
4. **Given** a request in `VISIT_IN_PROGRESS`, **When** the clinic nurse determines the patient is ineligible, **Then** the system prompts for a mandatory reason, updates `WC_REQ_STATUS` to `NOT_ELIGIBLE`, records the reason in `WC_REQ_NOTES`, and does NOT create a registration record.

---

### User Story 3 - Automated In-Database Registration Generation (Priority: P1)

As the management system, when a referral request is marked `ACCEPTED`, the database tier must automatically instantiate an active clinical registration (`wc_registration_tb`) without relying on manual duplicate data entry or application-layer secondary writes.

**Why this priority**: Fulfills Database-First Architecture (Constitution Principle II) and guarantees transactional data integrity.

**Independent Test**: Can be tested independently by transitioning a request status to `ACCEPTED` and verifying that a `wc_registration_tb` record appears with status `ACTIVE`, referencing the request code, patient code, default Wound Care Clinic department code, clinic nurse code, and entry user code.

**Acceptance Scenarios**:

1. **Given** a request status set to `ACCEPTED`, **When** the database procedure/trigger runs, **Then** a new record is inserted into `wc_registration_tb` with `WC_REG_STATUS = 'ACTIVE'`, linking `WC_REG_REQUEST_CD`, `WC_REG_PATIENT_CD`, `WC_REG_DEPARTMENT_CD` (default Wound Care Clinic department code), `WC_REG_NURSE_CD`, `WC_REG_ENTRY_USER_CD`, and `WC_REG_DATE`.
2. **Given** a request that is already `ACCEPTED`, `NOT_ELIGIBLE`, or `CANCELLED`, **When** any secondary approval/acceptance attempt occurs, **Then** the database enforces strict state-machine rules and aborts the transition.

---

### User Story 4 - Request Cancellation & Referral Tracking ("My Requests") (Priority: P2)

As a ward nurse, I want to track all referral requests I submitted and cancel any request that is still `PENDING` if it was submitted in error or the patient was discharged before the visit.

**Why this priority**: Provides auditability for ward staff and allows self-service cleanup of erroneous pending requests.

**Independent Test**: Can be tested independently by logging in as a ward nurse, viewing "My Submitted Referrals", and clicking cancel on a `PENDING` request to verify its status changes to `CANCELLED`.

**Acceptance Scenarios**:

1. **Given** a logged-in nurse, **When** they navigate to "My Requests" ("طلباتي"), **Then** the system lists all requests entered by their user code (`WC_REQ_ENTRY_USER_CD`), displaying status badges in Arabic (`قيد الانتظار`, `زيارة قيد التنفيذ`, `مقبول`, `غير مؤهل`, `ملغى`).
2. **Given** a request in `PENDING` status, **When** the submitting nurse clicks "Cancel Request" ("إلغاء الطلب") and confirms, **Then** the system updates `WC_REQ_STATUS` to `CANCELLED`.
3. **Given** a request in `VISIT_IN_PROGRESS`, `ACCEPTED`, or `NOT_ELIGIBLE` status, **When** viewed by the submitting nurse, **Then** the cancel option is disabled.

---

### Edge Cases

- What happens if a ward nurse attempts to submit a referral for a patient who already has an active registration (`wc_registration_tb`)? The system alerts the nurse in Arabic ("المريض لديه تسجيل نشط حالياً في عيادة العناية بالجروح") and prevents creating a duplicate request.
- What happens if a patient is transferred to another ward while a request is `PENDING`? The clinic nurse can view updated hospital location data during the visit before completing intake.
- What happens if the external hospital system sync is unavailable? Local search operates against `mr_patient_tb`, with mock data fallback for development environment.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a patient lookup interface by National ID (`MR_PAT_ID_NO`) or Full Name (`MR_PAT_FULL_NAME`).
- **FR-002**: System MUST sync patient master data from the external hospital index into `mr_patient_tb` when searching for an unindexed patient.
- **FR-003**: System MUST record patient snapshot attributes (`WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`) upon request submission into `wc_request_reg_tb`.
- **FR-004**: System MUST distinguish between the clinical employee (`WC_REQ_NURSE_CD` referencing `per_employee_tb`) and the data-entry user (`WC_REQ_ENTRY_USER_CD` referencing `per_users_tb`).
- **FR-005**: System MUST enforce the referral lifecycle status state machine: `PENDING` → `VISIT_IN_PROGRESS` → (`ACCEPTED` | `NOT_ELIGIBLE`), along with `CANCELLED` from `PENDING`.
- **FR-006**: System MUST record the reviewing clinic employee (`WC_REQ_REVIEWED_BY_CD` referencing `per_employee_tb`) when a visit is initiated or completed.
- **FR-007**: System MUST execute in-database logic (trigger or stored procedure) upon status change to `ACCEPTED` to automatically create an active clinical registration record in `wc_registration_tb` with `WC_REG_DEPARTMENT_CD` assigned to the default Wound Care Clinic department code (`WC_DEPT_CODE`).
- **FR-008**: System MUST allow submitting nurses to cancel their own `PENDING` requests.
- **FR-009**: System MUST prevent duplicate `PENDING` requests for a patient who already has a pending referral or an `ACTIVE` clinic registration.
- **FR-010**: System MUST render all forms, status indicators, queues, and messages in Arabic with complete Right-to-Left (RTL) layout.
- **FR-011**: System MUST convert static referral submission and queue mockups into Blade views integrated into the shared layout without introducing any database schema migrations or column modifications.

### Key Entities *(include if feature involves data)*

- **Registration Request (`wc_request_reg_tb`)**:
  - `WC_REQ_CODE` (PK, CHAR(36) UUID string)
  - `WC_REQ_PATIENT_CD` (FK to `mr_patient_tb.MR_PAT_CODE`)
  - `WC_REQ_PAT_NAME_SNAPSHOT` (String 150)
  - `WC_REQ_PAT_ID_SNAPSHOT` (String 50)
  - `WC_REQ_PAT_PHONE_SNAPSHOT` (String 30, nullable)
  - `WC_REQ_NURSE_CD` (FK to `per_employee_tb.PER_EMP_CODE`, requesting ward nurse)
  - `WC_REQ_ENTRY_USER_CD` (FK to `per_users_tb.PER_USER_CODE`, data-entry user)
  - `WC_REQ_REVIEWED_BY_CD` (FK to `per_employee_tb.PER_EMP_CODE`, clinic nurse, nullable)
  - `WC_REQ_STATUS` (String 20: `PENDING`, `VISIT_IN_PROGRESS`, `ACCEPTED`, `NOT_ELIGIBLE`, `CANCELLED`, default `PENDING`)
  - `WC_REQ_NOTES` (Text, referral reason & visit notes, nullable)
  - `WC_REQ_CREATED_AT` (DateTime)

- **Clinical Registration (`wc_registration_tb`)**:
  - `WC_REG_CODE` (PK, CHAR(36) UUID string)
  - `WC_REG_REQUEST_CD` (FK to `wc_request_reg_tb.WC_REQ_CODE`)
  - `WC_REG_PATIENT_CD` (FK to `mr_patient_tb.MR_PAT_CODE`)
  - `WC_REG_DEPARTMENT_CD` (FK to `wc_department_tb.WC_DEPT_CODE`, default Wound Care Clinic department code)
  - `WC_REG_PROCEDURE_CD` (FK to `wc_procedure_tb.WC_PROC_CODE`, nullable)
  - `WC_REG_PMH_NOTES` (Text, nullable)
  - `WC_REG_DATE` (DateTime)
  - `WC_REG_NURSE_CD` (FK to `per_employee_tb.PER_EMP_CODE`)
  - `WC_REG_ENTRY_USER_CD` (FK to `per_users_tb.PER_USER_CODE`)
  - `WC_REG_STATUS` (String 20: `ACTIVE`, `DISCHARGED`, default `ACTIVE`)

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Ward nurses can search for a patient and submit a referral request in under 45 seconds.
- **SC-002**: 100% of `ACCEPTED` referral requests automatically instantiate a corresponding `wc_registration_tb` record via database logic without manual secondary creation.
- **SC-003**: 100% of request records accurately distinguish between requesting employee (`WC_REQ_NURSE_CD`), reviewing employee (`WC_REQ_REVIEWED_BY_CD`), and data-entry user (`WC_REQ_ENTRY_USER_CD`).
- **SC-004**: 0 database migration files or column changes are added to the codebase during Phase 4 implementation.
- **SC-005**: All UI screens load cleanly in Arabic with 100% RTL layout compliance.

## Assumptions

- Phase 4 utilizes the existing Phase 1 database schema (`database/migrations/2026_09_20_000001_create_wccms_core_tables.php`) without any database structural modifications.
- Hospital patient search and admission info retrieval use local `mr_patient_tb` data backed by a mock service driver for hospital system integration during local development.
- Detailed clinical registration information (diagnoses, wounds, procedures, dressings, medications) is captured in Phase 5 — Registration & Diagnosis.
