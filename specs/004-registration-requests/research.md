# Phase 0 Research: Phase 4 — Registration Requests

**Feature**: [`spec.md`](spec.md) | **Branch**: `004-registration-requests`

## Research Findings & Architectural Decisions

### 1. Ward Referral Search & Patient Lookup Integration

- **Decision**: Implement patient lookup via a dedicated query endpoint (`/requests/search-patient`) backed by `MrPatient` model queries against `mr_patient_tb`, with fallback to a mock `HospitalPatientSyncService` when a patient National ID is not found locally.
- **Rationale**: Meets Constitution Principle VI (MySQL-first development) and keeps hospital integration modular behind a service interface (`HospitalPatientSyncServiceInterface`).
- **Data Returned**: Patient Demographics (ID number, Full Name, Gender, DOB, Phone) and Current Hospital Admission context (Ward/Department, Attending Doctor, Admission Date).

### 2. Referral Lifecycle State Machine

- **Decision**: Implement state transitions strictly through model methods / stored procedure calls in `WcRequestReg`:
  - `PENDING`: Newly submitted referral by ward nurse (`WC_REQ_STATUS = 'PENDING'`).
  - `VISIT_IN_PROGRESS`: Clinic nurse claims referral and visits patient in ward (`WC_REQ_STATUS = 'VISIT_IN_PROGRESS'`, sets `WC_REQ_REVIEWED_BY_CD`).
  - `ACCEPTED`: Clinic nurse accepts patient (`WC_REQ_STATUS = 'ACCEPTED'`, triggers in-database `wc_registration_tb` creation).
  - `NOT_ELIGIBLE`: Clinic nurse declines patient (`WC_REQ_STATUS = 'NOT_ELIGIBLE'`, records justification in `WC_REQ_NOTES`).
  - `CANCELLED`: Submitting ward nurse cancels pending referral (`WC_REQ_STATUS = 'CANCELLED'`).
- **Rationale**: Enforces explicit state transitions, prevents race conditions, and guarantees clear auditability.

### 3. In-Database Registration Creation (Constitution Principle II)

- **Decision**: Trigger / Stored Procedure `sp_accept_registration_request(request_code, clinic_nurse_cd, entry_user_cd)`:
  - Validates request is in state `PENDING` or `VISIT_IN_PROGRESS`.
  - Updates `wc_request_reg_tb.WC_REQ_STATUS` to `'ACCEPTED'` and sets `WC_REQ_REVIEWED_BY_CD`.
  - Retrieves default Wound Care Clinic department code from `wc_department_tb`.
  - Inserts a new record into `wc_registration_tb`:
    - `WC_REG_CODE`: generated UUID
    - `WC_REG_REQUEST_CD`: `request_code`
    - `WC_REG_PATIENT_CD`: `WC_REQ_PATIENT_CD`
    - `WC_REG_DEPARTMENT_CD`: Default Wound Care Clinic department code
    - `WC_REG_DATE`: Current timestamp
    - `WC_REG_NURSE_CD`: `clinic_nurse_cd`
    - `WC_REG_ENTRY_USER_CD`: `entry_user_cd`
    - `WC_REG_STATUS`: `'ACTIVE'`
- **Rationale**: Strictly complies with Constitution Principle II (Database-First Architecture & In-Database Logic). Eloquent controller logic calls this DB routine rather than creating `wc_registration_tb` records in PHP.

### 4. Controller & Authorization Design

- **Decision**: `RegistrationRequestController` with routes:
  - `GET /requests` (`requests.index`) - Clinic incoming queue (clinic nurses / admin).
  - `GET /requests/my` (`requests.my`) - Ward nurse submitted referrals list.
  - `GET /requests/create` (`requests.create`) - Ward referral submission form.
  - `POST /requests` (`requests.store`) - Submit referral request.
  - `GET /requests/{request}` (`requests.show`) - View referral details & visit intake interface.
  - `POST /requests/{request}/visit` (`requests.visit`) - Transition to `VISIT_IN_PROGRESS`.
  - `POST /requests/{request}/accept` (`requests.accept`) - Accept patient & execute stored procedure.
  - `POST /requests/{request}/ineligible` (`requests.ineligible`) - Mark patient ineligible with notes.
  - `POST /requests/{request}/cancel` (`requests.cancel`) - Ward nurse cancels pending referral.
- **Rationale**: RESTful naming aligned with Laravel conventions and existing route middleware (`hospital.jwt`, `role:NURSE,ADMIN`).

### 5. Blade Component & UI Integration

- **Decision**: Create Blade views in `resources/views/requests/`:
  - `create.blade.php`: Arabic RTL patient search, hospital admission display, and referral form.
  - `index.blade.php`: Clinic queue of incoming referrals with status badges and action buttons.
  - `my_requests.blade.php`: Submitting nurse's personal referral tracking list.
  - `show.blade.php`: Ward visit clinical intake form for clinic nurses.
- **Rationale**: Leverages Phase 2 UI Foundation components (`<x-card>`, `<x-data-table>`, `<x-status-badge>`, `@vite` asset bundling) and guarantees 100% Arabic RTL compliance (Constitution Principle IV).
