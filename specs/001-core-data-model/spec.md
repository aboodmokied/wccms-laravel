# Feature Specification: Phase 1 — Core Data Model

**Feature Branch**: `001-core-data-model`

**Created**: 2026-09-20

**Status**: Draft

**Input**: User description: "Read plan.md and create specification for Phase 1 — Core Data Model."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Master Reference Data & System Foundation (Priority: P1)

As a system developer and administrator, I need the complete set of 7 master reference tables and 2 core identity tables (users & employees) initialized with seed data so that clinical workflow forms can reference valid departments, medical history options, wound types, dressings, medications, procedures, and diagnoses.

**Why this priority**: Without core reference tables and employee/user identity models, no clinical registration or review data can be stored or linked.

**Independent Test**: Can be fully tested by running database migrations and seeders, verifying that all 16 tables contain valid active records and that custom primary key (`CODE`) and foreign key (`_CD`) contracts conform to the naming protocol.

**Acceptance Scenarios**:

1. **Given** a fresh database environment, **When** migrations are executed, **Then** all 16 tables are created with `CHAR(36)` primary keys, strict column prefixes, and explicit foreign key constraints.
2. **Given** database seeders are run, **Then** the 7 master-data reference tables (`WC_DEPARTMENT_TB`, `WC_PMH_TB`, `WC_DIAGNOSIS_TB`, `WC_WOUND_TYPE_TB`, `WC_DRESSING_TB`, `WC_MEDICATION_TB`, `WC_PROCEDURE_TB`) are populated with active default options.

---

### User Story 2 - Patient Registration Request & Clinical Registration Model (Priority: P2)

As a clinical supervisor and staff nurse, I need a data model that captures patient registrations and registration requests, including patient snapshot data, approval audit tracking, and 1:1 merged clinical histories, so that patient registration lifecycle states (Pending, Approved, Rejected, Cancelled) are accurately persisted.

**Why this priority**: Core clinical workflow begins with request submission and approval, creating the primary registration entity for wound care tracking.

**Independent Test**: Can be independently tested by inserting a registration request record into `WC_REQUEST_REG_TB`, approving it, and verifying that the registration record in `WC_REGISTRATION_TB` links correctly to `MR_PATIENT_TB`, `PER_EMPLOYEE_TB` (clinical actor), and `PER_USERS_TB` (data-entry user).

**Acceptance Scenarios**:

1. **Given** a patient registration request submission, **When** saved to `WC_REQUEST_REG_TB`, **Then** it stores patient snapshot fields (`WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`), the requesting nurse (`WC_REQ_NURSE_CD`), data-entry user (`WC_REQ_ENTRY_USER_CD`), and initial status `PENDING`.
2. **Given** a supervisor approves or rejects a request, **When** updated, **Then** `WC_REQ_REVIEWED_BY_CD` records the approving employee (`PER_EMPLOYEE_TB`), `WC_REQ_REVIEW_NOTES` records notes, and status transitions to `APPROVED` or `REJECTED`.
3. **Given** an approved request, **When** `WC_REGISTRATION_TB` is created, **Then** it merges past medical history and procedure data in a single record, maintaining dual audit references (`WC_REG_NURSE_CD` for nurse employee and `WC_REG_ENTRY_USER_CD` for system user).

---

### User Story 3 - Daily Review & Scheduled Review Tracking (Priority: P3)

As a wound care practitioner, I need a header-detail model for daily visits (`WC_DAILY_REVIEW_TB` and `WC_DAILY_REVIEW_DETAIL_TB`) and scheduled appointments (`WC_SCHEDULED_REVIEW_TB`) with automatic status invalidation support so that patient wound progress and future reviews are systematically tracked.

**Why this priority**: Daily review records capture ongoing clinical assessments and wound outcome transitions across multiple visits.

**Independent Test**: Can be tested independently by creating a visit header, attaching wound details for multiple diagnoses, and scheduling/voiding review appointments.

**Acceptance Scenarios**:

1. **Given** a daily patient visit, **When** saved, **Then** one `WC_DAILY_REVIEW_TB` header is created containing global visit metadata, linked to one or more `WC_DAILY_REVIEW_DETAIL_TB` rows recording per-wound observations.
2. **Given** a patient is re-registered at the clinic, **When** a new active registration is created, **Then** any pending scheduled reviews (`WC_SCHEDULED_REVIEW_TB`) tied to previous registrations transition to `VOIDED`.

---

### Edge Cases

- **Patient Snapshot Mismatch**: When patient demographic details in `MR_PATIENT_TB` change after a request is submitted, `WC_REQUEST_REG_TB` retains the original snapshot data as submitted.
- **Multiple Simultaneous Diagnoses**: A registration can have multiple diagnoses in `WC_REG_DIAGNOSIS_TB`, each tracked independently in daily review details.
---

## Clarifications

### Session 2026-09-20
- Q: How should historical clinical records (registrations, requests, daily reviews) be handled when archived? → A: Soft-flag archiving only (relying on status fields: `CANCELLED`, `VOIDED`, `DISCHARGED`); physical row deletions are strictly prohibited to ensure full medical audit compliance.
- Q: How should default baseline records for master-data tables be seeded across environments? → A: Dedicated modular Laravel Seeder classes (using `firstOrCreate`) called by `DatabaseSeeder` for idempotent environment bootstrapping.
- Q: What indexing policy should be enforced across foreign keys, status columns, and patient IDs? → A: Index all foreign key columns (`_CD`), status flags (`STATUS`), and unique index on patient ID (`MR_PAT_ID_NO`).

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST define all 16 database tables adhering strictly to `<PREFIX>_<name>_TB` table naming, `<short_prefix>_` column prefixes, `PREFIX_CODE` custom PKs, and `_CD` FK suffixes.
- **FR-002**: Database schema MUST enforce `CHAR(36)` UUID format for all primary keys across all domain and master tables.
- **FR-003**: System MUST provide database seeders for 7 master reference tables: `WC_DEPARTMENT_TB`, `WC_PMH_TB`, `WC_DIAGNOSIS_TB`, `WC_WOUND_TYPE_TB`, `WC_DRESSING_TB`, `WC_MEDICATION_TB`, and `WC_PROCEDURE_TB`.
- **FR-004**: System MUST record patient snapshot columns on `WC_REQUEST_REG_TB` (`WC_REQ_PAT_NAME_SNAPSHOT`, `WC_REQ_PAT_ID_SNAPSHOT`, `WC_REQ_PAT_PHONE_SNAPSHOT`) to preserve historical request integrity.
- **FR-005**: System MUST include a `WC_REQ_REVIEWED_BY_CD` column on `WC_REQUEST_REG_TB` referencing `PER_EMPLOYEE_TB` to track supervisor approval/rejection decisions.
- **FR-006**: System MUST maintain explicit actor vs. data-entry separation across clinical tables, distinguishing `PER_EMPLOYEE_TB` (clinical actor, e.g. `WC_REG_NURSE_CD`, `WC_DREV_NURSE_CD`) from `PER_USERS_TB` (system data-entry user, e.g. `WC_REG_ENTRY_USER_CD`, `WC_DREV_ENTRY_USER_CD`).
- **FR-007**: `WC_REGISTRATION_TB` MUST merge core registration metadata, primary procedure details, and past medical history summary into a 1:1 consolidated registration record.
- **FR-008**: System MUST support multiple diagnoses per registration via `WC_REG_DIAGNOSIS_TB`, with explicit audit columns for data-entry user (`WC_RDIAG_ENTRY_USER_CD`) and clinical employee (`WC_RDIAG_CLINICAL_EMP_CD`).
- **FR-009**: System MUST implement a header-detail model for daily review visits: `WC_DAILY_REVIEW_TB` (visit header) and `WC_DAILY_REVIEW_DETAIL_TB` (wound assessment details).
- **FR-010**: `WC_SCHEDULED_REVIEW_TB` MUST store a sequential `WC_SREV_VISIT_SERIAL` integer field tracking the expected visit number for that registration.
- **FR-011**: `WC_SCHEDULED_REVIEW_TB` MUST support a `VOIDED` status state to represent appointments automatically invalidated upon patient re-registration.
- **FR-012**: Eloquent Models MUST define `$table`, `$primaryKey = 'PREFIX_CODE'`, `$incrementing = false`, `$keyType = 'string'`, and `$timestamps = false` in compliance with the Database-First architecture.
- **FR-013**: Migrations MUST use Laravel fluent schema builder without native ENUM or BOOLEAN types to enable seamless migration from local MySQL to Oracle SQL Server.
- **FR-014**: System MUST strictly enforce soft-flag archiving (via status fields `CANCELLED`, `VOIDED`, `DISCHARGED`, `IS_ACTIVE`) across all domain tables, prohibiting physical row deletions to maintain medical audit compliance.
- **FR-015**: Migrations MUST explicitly index all foreign key columns (`_CD`), status columns (`WC_REQ_STATUS`, `WC_SREV_STATUS`, `WC_REG_STATUS`), and enforce a unique index on `MR_PAT_ID_NO`.

### Key Entities & Schema Structure

1. **`PER_USERS_TB`**: System user accounts
   - `PER_USER_CODE`: `CHAR(36)` Primary Key
   - `PER_USER_USERNAME`: `VARCHAR(100)` Unique username
   - `PER_USER_PASSWORD`: `VARCHAR(255)` Password hash
   - `PER_USER_ROLE`: `VARCHAR(20)` Role (`NURSE`, `ADMIN`)
   - `PER_USER_IS_ACTIVE`: `SMALLINT` Active flag (1/0)

2. **`PER_EMPLOYEE_TB`**: Hospital employee directory
   - `PER_EMP_CODE`: `CHAR(36)` Primary Key
   - `PER_EMP_FULL_NAME`: `VARCHAR(150)` Employee full name
   - `PER_EMP_JOB_TITLE`: `VARCHAR(100)` Job title
   - `PER_EMP_USER_CD`: `CHAR(36)` Foreign Key to `PER_USERS_TB` (Nullable)

3. **`MR_PATIENT_TB`**: Patient master index
   - `MR_PAT_CODE`: `CHAR(36)` Primary Key
   - `MR_PAT_ID_NO`: `VARCHAR(50)` National/Hospital ID number
   - `MR_PAT_FULL_NAME`: `VARCHAR(150)` Patient full name
   - `MR_PAT_PHONE`: `VARCHAR(30)` Contact phone number
   - `MR_PAT_GENDER`: `VARCHAR(10)` Gender (`MALE`, `FEMALE`)
   - `MR_PAT_DOB`: `DATE` Date of birth
   - `MR_PAT_LAST_SYNCED_AT`: `DATETIME` Hospital HIS sync timestamp

4. **`WC_REQUEST_REG_TB`**: Registration request queue
   - `WC_REQ_CODE`: `CHAR(36)` Primary Key
   - `WC_REQ_PATIENT_CD`: `CHAR(36)` Foreign Key to `MR_PATIENT_TB`
   - `WC_REQ_PAT_NAME_SNAPSHOT`: `VARCHAR(150)` Snapshot of patient full name
   - `WC_REQ_PAT_ID_SNAPSHOT`: `VARCHAR(50)` Snapshot of patient national ID
   - `WC_REQ_PAT_PHONE_SNAPSHOT`: `VARCHAR(30)` Snapshot of patient phone number
   - `WC_REQ_NURSE_CD`: `CHAR(36)` Foreign Key to `PER_EMPLOYEE_TB` (Requesting Nurse)
   - `WC_REQ_ENTRY_USER_CD`: `CHAR(36)` Foreign Key to `PER_USERS_TB` (Data Entry User)
   - `WC_REQ_REVIEWED_BY_CD`: `CHAR(36)` Foreign Key to `PER_EMPLOYEE_TB` (Reviewing Supervisor)
   - `WC_REQ_STATUS`: `VARCHAR(20)` Status (`PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`)
   - `WC_REQ_NOTES`: `TEXT` Supervisor review notes
   - `WC_REQ_CREATED_AT`: `DATETIME` Request submission timestamp

5. **`WC_REGISTRATION_TB`**: Active clinical registration (1:1 merged model)
   - `WC_REG_CODE`: `CHAR(36)` Primary Key
   - `WC_REG_REQUEST_CD`: `CHAR(36)` Foreign Key to `WC_REQUEST_REG_TB`
   - `WC_REG_PATIENT_CD`: `CHAR(36)` Foreign Key to `MR_PATIENT_TB`
   - `WC_REG_DEPARTMENT_CD`: `CHAR(36)` Foreign Key to `WC_DEPARTMENT_TB`
   - `WC_REG_PROCEDURE_CD`: `CHAR(36)` Foreign Key to `WC_PROCEDURE_TB` (Nullable)
   - `WC_REG_PMH_NOTES`: `TEXT` Consolidated past medical history notes
   - `WC_REG_DATE`: `DATETIME` Registration timestamp
   - `WC_REG_NURSE_CD`: `CHAR(36)` Foreign Key to `PER_EMPLOYEE_TB` (Primary Clinical Nurse)
   - `WC_REG_ENTRY_USER_CD`: `CHAR(36)` Foreign Key to `PER_USERS_TB` (Data Entry User)
   - `WC_REG_STATUS`: `VARCHAR(20)` Status (`ACTIVE`, `DISCHARGED`, `CLOSED`)
   - `WC_REG_DISCHARGED_AT`: `DATETIME` Discharge timestamp (Nullable)
   - `WC_REG_DISCHARGED_BY_EMP_CD`: `CHAR(36)` Foreign Key to `PER_EMPLOYEE_TB` (Discharging Nurse)

6. **`WC_REG_DIAGNOSIS_TB`**: Registration diagnoses & wounds
   - `WC_RDIAG_CODE`: `CHAR(36)` Primary Key
   - `WC_RDIAG_REGISTRATION_CD`: `CHAR(36)` Foreign Key to `WC_REGISTRATION_TB`
   - `WC_RDIAG_DIAGNOSIS_CD`: `CHAR(36)` Foreign Key to `WC_DIAGNOSIS_TB`
   - `WC_RDIAG_WOUND_TYPE_CD`: `CHAR(36)` Foreign Key to `WC_WOUND_TYPE_TB`
   - `WC_RDIAG_CLINICAL_EMP_CD`: `CHAR(36)` Foreign Key to `PER_EMPLOYEE_TB` (Clinical Diagnostician)
   - `WC_RDIAG_ENTRY_USER_CD`: `CHAR(36)` Foreign Key to `PER_USERS_TB` (Data Entry User)

7. **`WC_DAILY_REVIEW_TB`**: Daily review visit header
   - `WC_DREV_CODE`: `CHAR(36)` Primary Key
   - `WC_DREV_REGISTRATION_CD`: `CHAR(36)` Foreign Key to `WC_REGISTRATION_TB`
   - `WC_DREV_SCHEDULED_REVIEW_CD`: `CHAR(36)` Foreign Key to `WC_SCHEDULED_REVIEW_TB` (Nullable)
   - `WC_DREV_VISIT_DATE`: `DATETIME` Visit timestamp
   - `WC_DREV_NURSE_CD`: `CHAR(36)` Foreign Key to `PER_EMPLOYEE_TB` (Attending Nurse)
   - `WC_DREV_ENTRY_USER_CD`: `CHAR(36)` Foreign Key to `PER_USERS_TB` (Data Entry User)
   - `WC_DREV_GENERAL_NOTES`: `TEXT` Overall visit summary notes

8. **`WC_DAILY_REVIEW_DETAIL_TB`**: Per-wound assessment detail
   - `WC_DREVD_CODE`: `CHAR(36)` Primary Key
   - `WC_DREVD_DAILY_REVIEW_CD`: `CHAR(36)` Foreign Key to `WC_DAILY_REVIEW_TB`
   - `WC_DREVD_REG_DIAGNOSIS_CD`: `CHAR(36)` Foreign Key to `WC_REG_DIAGNOSIS_TB`
   - `WC_DREVD_DRESSING_CD`: `CHAR(36)` Foreign Key to `WC_DRESSING_TB` (Nullable)
   - `WC_DREVD_MEDICATION_CD`: `CHAR(36)` Foreign Key to `WC_MEDICATION_TB` (Nullable)
   - `WC_DREVD_OBSERVATION`: `TEXT` Detailed clinical wound observation
   - `WC_DREVD_OUTCOME_STATUS`: `VARCHAR(30)` Assessment (`IMPROVED_DISCHARGE`, `IMPROVED_FOLLOWUP`, `WORSENED`, `DECEASED`)

9. **`WC_SCHEDULED_REVIEW_TB`**: Appointment schedule
   - `WC_SREV_CODE`: `CHAR(36)` Primary Key
   - `WC_SREV_REGISTRATION_CD`: `CHAR(36)` Foreign Key to `WC_REGISTRATION_TB`
   - `WC_SREV_SCHEDULED_DATE`: `DATETIME` Scheduled appointment date
   - `WC_SREV_VISIT_SERIAL`: `INTEGER` Sequential visit serial index per registration
   - `WC_SREV_STATUS`: `VARCHAR(20)` Appointment status (`PENDING`, `COMPLETED`, `MISSED`, `CANCELLED`, `VOIDED`)

10. **7 Master Reference Tables**:
    - `WC_DEPARTMENT_TB` (`WC_DEPT_CODE` PK, `WC_DEPT_NAME`, `WC_DEPT_IS_ACTIVE`)
    - `WC_PMH_TB` (`WC_PMH_CODE` PK, `WC_PMH_NAME`, `WC_PMH_IS_ACTIVE`)
    - `WC_DIAGNOSIS_TB` (`WC_DIAG_CODE` PK, `WC_DIAG_NAME`, `WC_DIAG_IS_ACTIVE`)
    - `WC_WOUND_TYPE_TB` (`WC_WTYPE_CODE` PK, `WC_WTYPE_NAME`, `WC_WTYPE_IS_ACTIVE`)
    - `WC_DRESSING_TB` (`WC_DRESS_CODE` PK, `WC_DRESS_NAME`, `WC_DRESS_IS_ACTIVE`)
    - `WC_MEDICATION_TB` (`WC_MED_CODE` PK, `WC_MED_NAME`, `WC_MED_IS_ACTIVE`)
    - `WC_PROCEDURE_TB` (`WC_PROC_CODE` PK, `WC_PROC_NAME`, `WC_PROC_IS_ACTIVE`)

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All 16 database tables migrate cleanly on MySQL with zero schema errors or syntax incompatibilities.
- **SC-002**: Database seeders populate all 7 master reference tables within 3 seconds during initial environment setup.
- **SC-003**: 100% of primary key and foreign key column definitions conform to the unified naming protocol (`PREFIX_CODE` PKs, `_CD` FKs, `<short_prefix>_` column prefixes).
- **SC-004**: 100% of clinical data mutation tables maintain strict dual audit tracking (`PER_EMPLOYEE_TB` for clinical actor, `PER_USERS_TB` for data-entry user).

---

## Assumptions

- **Snapshot Data**: Patient snapshot columns on `WC_REQUEST_REG_TB` store immutable copies of patient demographics at the time of request submission.
- **Supervisor Audit**: `WC_REQ_REVIEWED_BY_CD` references `PER_EMPLOYEE_TB` representing the supervisor who rendered the approval or rejection decision.
- **Visit Serial**: `WC_SREV_VISIT_SERIAL` is an integer counter (1, 2, 3...) representing the sequence of scheduled follow-up visits under a single registration.
- **Voided Status**: A distinct `VOIDED` status string is introduced on `WC_SCHEDULED_REVIEW_TB` to distinguish system-invalidated appointments from user-cancelled ones.
