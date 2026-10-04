# Feature Specification: Scheduled Reviews & Appointments Management

**Feature Branch**: `007-scheduled-reviews`

**Created**: 2026-10-03

**Status**: Draft

**Input**: User description: "Phase 7 — Scheduled Reviews: Appointment Calendar/List, Fulfilling Appointments via Daily Review, Status Management (Pending, Completed, Cancelled), and Hard Deletion of Old Pending Appointments on Re-Registration."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Viewing & Filtering Scheduled Review Appointments (Priority: P1)

As a Wound Care Clinic nurse or administrative staff member, I want to view upcoming, today's, overdue, and past scheduled review appointments across all registered patients, so that I can manage daily clinic operations and patient flow efficiently.

**Why this priority**: Managing appointment schedules and knowing which patients are expected today or overdue is critical to clinic workflow and patient care continuity.

**Independent Test**: Can be tested by creating registrations with pending scheduled reviews and verifying that the appointment queue correctly lists, filters, and sorts them by date and status.

**Acceptance Scenarios**:

1. **Given** there are pending scheduled review appointments for today and future dates, **When** clinic staff open the Scheduled Reviews page (`/reviews/scheduled`), **Then** the system displays a clear summary ribbon (Today's, Upcoming, Overdue, Completed) and a filterable queue of appointments.
2. **Given** clinic staff want to find appointments for a specific patient or date range, **When** they enter a patient name, ID, or date filter, **Then** the list updates dynamically to show matching scheduled appointments.

---

### User Story 2 - Fulfilling a Scheduled Appointment via Daily Review Visit (Priority: P1)

As a Wound Care Clinic nurse, when a patient arrives for their scheduled review appointment, I want to record their visit so that the appointment status updates to `COMPLETED` and is linked to the newly created daily review record.

**Why this priority**: Fulfilling appointments links scheduled care to actual clinical evaluation, ensuring medical records remain accurate and audit-compliant.

**Independent Test**: Can be tested by selecting a pending scheduled review, completing a daily review visit for the patient, and asserting that the appointment status transitions to `COMPLETED` with its `WC_DREV_SCHEDULED_REVIEW_CD` correctly set.

**Acceptance Scenarios**:

1. **Given** a patient with a `PENDING` scheduled review arrives at the clinic (even if the appointment date has passed), **When** the nurse clicks "Start Scheduled Visit", **Then** the system opens the multi-wound daily review workspace for that patient with the appointment reference attached.
2. **Given** the nurse completes and saves the daily review for a scheduled visit, **When** the form is submitted, **Then** the scheduled review status automatically transitions to `COMPLETED` and links to the newly created `WcDailyReview` record.

---

### User Story 3 - Manual Appointment Status Management & Cancellation (Priority: P2)

As a Wound Care Clinic nurse or supervisor, I want to manually cancel an appointment or update its scheduled date, so that clinic appointment logs accurately reflect patient attendance. A `PENDING` appointment stays `PENDING` indefinitely (even after its date passes) until staff complete or cancel it manually.

**Why this priority**: Patients frequently request date changes or cancel visits; resolution is entirely manual with zero automated status transitions.

**Independent Test**: Can be tested by updating a pending appointment's date or status to `CANCELLED`, and verifying database persistence and status badge updates.

**Acceptance Scenarios**:

1. **Given** a patient calls to cancel an appointment, **When** staff select "Cancel Appointment", **Then** the appointment updates its status to `CANCELLED` (with optional reason notes).
2. **Given** a patient requests a new appointment date, **When** staff select "Reschedule Date", **Then** the system updates `WC_SREV_SCHEDULED_DATE` accordingly while preserving its `PENDING` status.

---

### User Story 4 - Hard Deletion of Old Pending Appointments on Re-Registration (Priority: P2)

As a system, when a patient is newly registered at the Wound Care Clinic (a new active registration is created), I want to automatically hard-delete all `PENDING` scheduled appointments linked to their previous registrations from the database, so that stale appointments do not clutter clinic queues.

**Why this priority**: Enforces business logic and Database-First integrity, wiping outdated appointment records when a patient begins a new treatment cycle.

**Independent Test**: Can be tested by creating a new registration for a patient who has pending scheduled reviews under an old registration, and asserting that those old pending appointment rows are removed from `wc_scheduled_review_tb`.

**Acceptance Scenarios**:

1. **Given** a patient has an old registration with a `PENDING` scheduled review (whether overdue or future), **When** a new clinic registration is created and accepted for that patient, **Then** the system automatically hard-deletes those old pending scheduled review records from the database.

---

### Edge Cases

- What happens if a nurse attempts to complete an appointment that has been `CANCELLED` or hard-deleted? The system MUST block the action and display a user-friendly error message indicating that the appointment is no longer pending.
- How does the system handle an appointment whose scheduled date has passed without action? It remains `PENDING` indefinitely and appears under the `OVERDUE` filter tab, allowing staff to complete the visit or cancel it manually at any time.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a dedicated Scheduled Reviews management page (`/reviews/scheduled`) listing all clinic appointments with summary counts (Today, Upcoming, Overdue, Completed, Cancelled).
- **FR-002**: System MUST allow filtering appointments by status (`PENDING`, `COMPLETED`, `CANCELLED`), date range, patient name, and National ID.
- **FR-003**: System MUST enable launching a daily review evaluation directly from any `PENDING` scheduled review appointment (regardless of whether its target date is past or future).
- **FR-004**: System MUST automatically update the scheduled review status to `COMPLETED` when the associated daily review visit is created and saved.
- **FR-005**: System MUST allow clinic staff to manually cancel a `PENDING` appointment (`CANCELLED`) with optional cancellation notes.
- **FR-006**: System MUST allow clinic staff to reschedule a `PENDING` appointment to a new date.
- **FR-007**: System MUST automatically hard-delete all pending scheduled appointments (`PENDING`) belonging to previous registrations of a patient when a new active registration is created for that patient.
- **FR-008**: System MUST enforce dual audit tracking for appointment actions, recording the clinical employee (`PER_EMP_CODE`) and data-entry user (`PER_USER_CODE`).

### Key Entities

- **WcScheduledReview (`wc_scheduled_review_tb`)**:
  - `WC_SREV_CODE` (PK, UUID)
  - `WC_SREV_REGISTRATION_CD` (FK to `wc_registration_tb`)
  - `WC_SREV_SCHEDULED_DATE` (Date/Time)
  - `WC_SREV_VISIT_SERIAL` (Integer serial counter per registration)
  - `WC_SREV_STATUS` (Enum: `PENDING`, `COMPLETED`, `CANCELLED`)
  - `WC_SREV_NOTES` (Text notes/cancellation reason)
- **WcDailyReview (`wc_daily_review_tb`)**:
  - Linked to `WcScheduledReview` via `WC_DREV_SCHEDULED_REVIEW_CD`.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Clinic staff can search, filter, and view scheduled appointments for any patient in under 2 seconds.
- **SC-002**: Fulfilling a scheduled appointment transitions its status to `COMPLETED` and links the daily review record accurately in 100% of cases.
- **SC-003**: Hard deletion of stale pending appointments upon re-registration occurs with 100% accuracy without manual intervention.
- **SC-004**: 100% of appointment status changes record proper audit logs (employee and entry user).

## Clarifications

### Session 2026-10-03
- Q: How should clinic staff create manual scheduled review appointments for an active patient when outside the daily review outcome flow? → A: Option C (Manual appointment creation is disallowed; scheduled review appointments are generated exclusively through daily review outcomes when status is IMPROVED_SCHEDULED).
- Q: How should the system handle a PENDING scheduled review appointment whose target date has passed without any staff action recorded? → A: A PENDING scheduled review stays PENDING indefinitely even after its target date has passed (no cron/auto-job, no auto-transition to MISSED/CANCELLED). Resolution is 100% manual (staff can complete or cancel it at any time). Re-registration automatically hard-deletes all PENDING appointments belonging to previous registrations for that patient.

## Assumptions

- Scheduled review appointments originate exclusively from daily review outcomes (`IMPROVED_SCHEDULED`) when a patient is discharged with a follow-up appointment. Manual creation of standalone appointments is out of scope.
- Overdue appointments remain in `PENDING` status indefinitely until manually resolved by staff or deleted by a new patient registration.
- Standard clinic operating hours apply for appointment scheduling.
- MySQL triggers / Eloquent database transaction hooks enforce state integrity.
