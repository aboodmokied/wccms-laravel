# Phase 0: Technical Research & Design Decisions — Scheduled Reviews

## Feature Branch: `007-scheduled-reviews`

---

## 1. Technical Choices & Rationale

### Research Topic 1: Origin & Lifecycle of Scheduled Reviews
- **Question**: How are scheduled review appointments generated, and can staff create them manually outside daily reviews?
- **Decision**: Scheduled reviews (`wc_scheduled_review_tb`) are created **exclusively** when a daily review visit (`wc_daily_review_tb`) or registration discharge evaluation records a follow-up requirement (e.g. `WC_DREV_OVERALL_STATUS` = `IMPROVED_SCHEDULED`). Standalone manual appointment creation is explicitly **disallowed**.
- **Rationale**: Follows clinic workflow rules established in specification clarification. Every scheduled appointment represents a clinically-mandated follow-up visit linked to a patient's active registration.
- **Alternatives Considered**:
  - *Allowing ad-hoc manual appointment creation*: Rejected because it bypasses clinical daily review evaluation and risks creating orphan appointment records not tied to treatment outcome states.

---

### Research Topic 2: Overdue Pending Appointment Lifecycle
- **Question**: How should the system handle a `PENDING` appointment whose scheduled date has passed without staff action?
- **Decision**: A `PENDING` appointment remains in `PENDING` status **indefinitely**, appearing under the `OVERDUE` filter tab on the scheduled review dashboard until explicitly fulfilled or cancelled by clinic staff. No background jobs, cron tasks, or automatic status changes (e.g. to `MISSED` or `EXPIRED`) will run.
- **Rationale**: Per user clarification in Session 2026-10-03. Patients frequently arrive late or miss dates, and clinic staff need full manual control to either complete the visit upon patient arrival or cancel it manually.
- **Alternatives Considered**:
  - *Cron job marking overdue appointments as `MISSED` after 24 hours*: Rejected per explicit user constraint ("Never auto-transitions... resolution is entirely manual").

---

### Research Topic 3: Hard Deletion of Pending Appointments on Re-Registration
- **Question**: How and when should old `PENDING` scheduled review appointments be cleaned up when a patient is newly registered?
- **Decision**: When a new registration is accepted for a patient (e.g. in `RegistrationRequestController@accept` / `WcRegistrationService`), the system executes a DB hard deletion (`DELETE FROM wc_scheduled_review_tb`) targeting all `PENDING` scheduled appointments associated with any *previous* registrations for that patient (`mr_patient_tb.MR_PAT_CODE`).
- **Implementation Mechanism**:
  ```php
  // Query all previous registrations for the patient
  $oldRegCodes = WcRegistration::where('WC_REG_PATIENT_CD', $patientCode)
      ->where('WC_REG_CODE', '!=', $newRegCode)
      ->pluck('WC_REG_CODE');

  // Hard delete all pending scheduled reviews tied to old registrations
  WcScheduledReview::whereIn('WC_SREV_REGISTRATION_CD', $oldRegCodes)
      ->where('WC_SREV_STATUS', 'PENDING')
      ->delete();
  ```
- **Rationale**: Fulfills Constitution Principle II & IV and FR-007. Old treatment episodes should not leave orphan pending appointments cluttering active queues when a patient re-enrolls in the clinic under a fresh registration.
- **Alternatives Considered**:
  - *Soft deletion or status update to `VOIDED`*: Rejected per explicit user clarification ("hard-deleted from the database, not transitioned to a new status like VOIDED").

---

### Research Topic 4: Fulfilling a Scheduled Appointment via Daily Review Visit
- **Question**: How does starting a scheduled visit transition the appointment to `COMPLETED`?
- **Decision**:
  1. Nurse clicks "Start Scheduled Visit" on `/reviews/scheduled` for a `PENDING` appointment.
  2. The system redirects to the daily review creation form (`/daily-reviews/create?registration_code=...&scheduled_review_code=...`).
  3. When the nurse submits the daily review, inside a DB transaction:
     - The `WcDailyReview` is created with `WC_DREV_SCHEDULED_REVIEW_CD = $scheduledReviewCode`.
     - The `WcScheduledReview` updates `WC_SREV_STATUS` to `COMPLETED` and sets `WC_SREV_COMPLETED_ON = now()`.
- **Rationale**: Ensures atomic linkage between the appointment and the actual clinical evaluation, satisfying SC-002 and FR-004.

---

### Research Topic 5: Manual Rescheduling & Cancellation
- **Question**: How are appointments rescheduled or cancelled by staff?
- **Decision**:
  - **Reschedule**: `PUT /reviews/scheduled/{scheduledReview}/reschedule` accepts a new `scheduled_date` (DATETIME) and updates `WC_SREV_SCHEDULED_DATE` while keeping status `PENDING`.
  - **Cancel**: `POST /reviews/scheduled/{scheduledReview}/cancel` accepts optional `notes` (reason for cancellation) and updates `WC_SREV_STATUS` to `CANCELLED`.
- **Audit Requirement**: Updates record `WC_SREV_CREATED_BY_CD` or dual audit context (entry user `PER_USER_CODE` and clinical employee `PER_EMP_CODE`).

---

## 2. Summary of Design Decisions

| Aspect | Decision | Rationale |
|---|---|---|
| **Appointment Creation** | Derived only from daily review outcomes | Disallows unanchored standalone appointments |
| **Overdue Handling** | Stays `PENDING` indefinitely; shown in `OVERDUE` filter tab | 100% manual staff resolution; no automated background cron jobs |
| **Re-Registration Cleanup** | Hard DB deletion of old `PENDING` appointments | Enforces clean queues when new active registration is created |
| **Fulfillment Linkage** | Links `WcDailyReview` via `WC_DREV_SCHEDULED_REVIEW_CD` & sets `WC_SREV_STATUS = COMPLETED` | Atomic transaction guarantees audit accuracy |
| **RTL & Localization** | Complete Arabic UI with Bootstrap 5 RTL styling and badges | Satisfies Constitution Principle IV |
