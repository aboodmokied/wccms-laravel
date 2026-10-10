# Feature Specification: Phase 5A — Longitudinal Patient Medical Record

**Feature Branch**: 011-longitudinal-patient-record

**Created**: 2026-10-08

**Status**: Draft

**Input**: User description: "Phase 5A — Longitudinal Patient Medical Record: show the patient's current and previous registrations over time from the patient page, while keeping each registration's clinical record clearly separated."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View the Complete Registration History (Priority: P1)

As an authorized clinic staff member, I want to see every Wound Care Clinic registration for a patient from one patient page, so that I can understand the patient's complete history instead of seeing only the current registration.

**Why this priority**: This is the core value of the feature. A longitudinal record is incomplete if any previous registration is omitted or difficult to identify.

**Independent Test**: Open the record of a patient who has one active registration and at least two discharged registrations. Verify that all three registrations appear, the active registration appears first and is expanded, and previous registrations are ordered from newest to oldest.

**Acceptance Scenarios**:

1. **Given** a patient with one active registration and multiple previous registrations, **When** an authorized staff member opens the patient record, **Then** all registrations are shown with the active registration first and the previous registrations ordered from newest to oldest.
2. **Given** a patient with only historical registrations, **When** the patient record is opened, **Then** all historical registrations are shown from newest to oldest and the most recent one is expanded by default.
3. **Given** a patient with only one registration, **When** the patient record is opened, **Then** that registration is displayed as a complete record without an empty historical-record area.
4. **Given** multiple registration sections, **When** the staff member scans the page, **Then** each section clearly displays its registration status, registration date, department, responsible nurse, and discharge date when applicable.

---

### User Story 2 - Inspect a Previous Registration in Context (Priority: P1)

As an authorized clinic staff member, I want to expand a previous registration and review only the clinical events that belong to it, so that I can safely understand an earlier course of treatment without mixing it with the current course.

**Why this priority**: Clinical decisions depend on knowing which wounds, reviews, treatments, and outcomes belonged to each separate episode of care.

**Independent Test**: Use a patient with two registrations that contain different wounds, reviews, appointments, and photos. Expand each registration and verify that every clinical item appears only under its owning registration.

**Acceptance Scenarios**:

1. **Given** a collapsed historical registration, **When** the staff member expands it, **Then** its procedure, past medical history notes, wounds, reviews, appointments, and wound photos become visible.
2. **Given** two registrations with different wounds and visits, **When** both are inspected, **Then** no wound, review, appointment, or photo from one registration appears under the other.
3. **Given** a registration with multiple reviews, **When** its history is displayed, **Then** reviews are ordered from newest to oldest within that registration.
4. **Given** a wound photo documented during a review, **When** that review is opened, **Then** the photo appears with the correct wound and review, and the wound's initial baseline photo remains available for comparison.
5. **Given** a registration with no reviews or no photos, **When** it is expanded, **Then** the page shows a clear empty state for the missing information without hiding the rest of the registration.

---

### User Story 3 - Navigate Between the Patient History and a Registration (Priority: P2)

As an authorized clinic staff member, I want to open the dedicated page for any registration from the longitudinal record and return to the patient history, so that I can perform registration-specific work without losing access to the overall history.

**Why this priority**: The longitudinal view supports clinical context, while detailed registration workflows remain necessary for focused actions and documentation.

**Independent Test**: Select a previous registration from the patient record, open its dedicated record, and verify that the selected registration matches the patient and remains distinct from the current registration.

**Acceptance Scenarios**:

1. **Given** any registration in the patient history, **When** the staff member chooses to open its detailed record, **Then** the correct registration record is displayed.
2. **Given** a discharged historical registration, **When** its dedicated record is opened, **Then** its historical clinical data remains readable and actions prohibited by its status remain unavailable.
3. **Given** the staff member returns to the patient record, **When** the longitudinal view reloads, **Then** the full registration history is still available in the required order.

---

### User Story 4 - Review Clinical Accountability Across Time (Priority: P2)

As an authorized clinic staff member, I want each historical clinical event to identify the employee who performed it and the user who entered it, so that the longitudinal record remains suitable for clinical and administrative audit.

**Why this priority**: Combining multiple episodes of care increases the risk of confusing who performed and who recorded an action unless both identities stay attached to the correct event.

**Independent Test**: Open a patient history containing events performed by one employee and entered by another user. Verify that both identities appear with the correct registration and event.

**Acceptance Scenarios**:

1. **Given** a review whose clinical actor differs from its data-entry user, **When** the review is displayed, **Then** both identities are shown separately and accurately.
2. **Given** clinical events across multiple registrations, **When** the patient history is inspected, **Then** audit identities remain attached to their original events and are not inherited from another registration.

### Edge Cases

- A patient has no registrations: show a clear empty state and retain the patient identity summary.
- A patient has historical registrations but no active registration: expand the most recent historical registration by default.
- A registration lacks optional procedure, past medical history, discharge, appointment, review, or photo data: label only the missing field or section without suppressing other available data.
- Two registrations share the same registration date: apply a stable newest-first order so the displayed order does not change between page loads.
- Historical data contains more than one registration marked active: show all records and their actual statuses, place the most recent active registration first, and do not silently omit or relabel conflicting records.
- A review is linked to a scheduled appointment from an older registration: display it only within that older registration.
- A wound photo has no linked review but is the initial baseline image: keep it associated with its wound and registration without presenting it as a review photo.
- A patient has a large history: keep registration boundaries and ordering intact while allowing older content to be revealed without making the initial page unusable.
- A related clinical record is unavailable or no longer accessible: show the remaining authorized history and a non-sensitive unavailable-data message in place of the missing item.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST provide an authorized patient-level medical record that includes every registration belonging to the selected patient.
- **FR-002**: The system MUST show patient identity information once at the patient level and MUST avoid presenting repeated patient demographics as separate patients.
- **FR-003**: The system MUST place an active registration before historical registrations. Within the same status grouping, registrations MUST be ordered from newest to oldest.
- **FR-004**: The system MUST expand the active registration by default. If no active registration exists, it MUST expand the most recent historical registration.
- **FR-005**: The system MUST present previous registrations as clearly labeled, individually expandable historical sections.
- **FR-006**: Each registration summary MUST show its status, registration date, department, responsible clinical employee, associated procedure when present, and discharge date when present.
- **FR-007**: The system MUST keep all diagnoses, wounds, reviews, appointments, photos, notes, and audit identities grouped under their owning registration.
- **FR-008**: The system MUST NOT combine reviews from separate registrations into one timeline.
- **FR-009**: For each registration, the system MUST display every associated wound and its diagnosis information.
- **FR-010**: For each registration, the system MUST display daily and scheduled review visits from newest to oldest and MUST visually distinguish scheduled visits from unscheduled daily visits.
- **FR-011**: Each displayed review MUST include its visit date and time, overall outcome, clinical employee, data-entry user, general notes when present, and per-wound evaluation details.
- **FR-012**: Each per-wound evaluation MUST display its wound identity, clinical observation when present, applied dressing and medication when present, and wound outcome.
- **FR-013**: Wound photos captured during a review MUST appear inside that review and under the correct wound.
- **FR-014**: An initial baseline wound photo MUST remain associated with its original wound and registration and MUST be available when comparing later review photos.
- **FR-015**: A wound photo, diagnosis, review, or appointment MUST appear no more than once within the longitudinal record.
- **FR-016**: For each registration, the system MUST display its scheduled appointments and their recorded states, including pending, completed, cancelled, missed, or voided where applicable.
- **FR-017**: Users MUST be able to open the dedicated detailed record for any displayed registration.
- **FR-018**: Historical registrations MUST remain read-only in the longitudinal patient view; any allowed clinical action MUST continue through the dedicated registration workflow and respect the registration's status.
- **FR-019**: The system MUST preserve the distinction between the clinical employee who performed an action and the authenticated user who recorded it wherever both are available.
- **FR-020**: The system MUST apply the existing patient-record authorization rules to the complete history and MUST NOT expose a registration the current user is not authorized to view.
- **FR-021**: The system MUST provide clear empty states for patients without registrations and for registration sections without wounds, reviews, appointments, or photos.
- **FR-022**: The longitudinal record MUST use Arabic user-facing content and native right-to-left presentation.
- **FR-023**: The initial patient record MUST remain usable for a patient with up to 10 registrations and 200 total reviews, while older or detailed content may be revealed on demand.
- **FR-024**: Ordering and grouping MUST remain deterministic when records share the same displayed date.

### Key Entities

- **Patient**: The person whose longitudinal Wound Care Clinic history is being viewed; owns zero or more registrations over time.
- **Registration**: A distinct episode of wound-care service for the patient, with its own dates, status, department, responsible employee, procedure, medical-history notes, and discharge information.
- **Wound / Diagnosis**: A wound documented within one registration; owns its clinical assessments and baseline image and must never move between registrations.
- **Clinical Review**: A dated visit within one registration, either daily or associated with a scheduled appointment; contains the overall outcome and audit identities.
- **Wound Assessment**: The evaluation of one wound during one clinical review, including observations, treatment, outcome, and review photos.
- **Scheduled Appointment**: A planned follow-up associated with one registration and, when completed, its resulting clinical review.
- **Wound Photo**: A baseline or review image associated with one wound and, for follow-up images, one wound assessment.
- **Clinical Employee**: The employee who performed the clinical action.
- **Data-Entry User**: The authenticated user who recorded the clinical information.

## Scope Boundaries

### Included

- A single patient page that aggregates current and previous Wound Care Clinic registrations.
- Read-only expansion of each registration's clinical history.
- Direct navigation to an individual registration's detailed record.
- Clear grouping, ordering, empty states, audit identities, appointments, and wound images.

### Excluded

- Merging two registrations or transferring clinical events between registrations.
- Editing historical clinical data directly from the longitudinal patient view.
- Comparing or combining records from different patients.
- Creating new clinical events outside the existing registration-specific workflows.
- Adding reporting, analytics, export, or print workflows.
- Correcting legacy data conflicts as part of this feature.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: For test patients with up to 20 registrations, 100% of registrations are discoverable from the patient record without searching for the patient again.
- **SC-002**: In validation scenarios, 100% of wounds, reviews, appointments, and photos appear only under their owning registration, with zero cross-registration duplicates.
- **SC-003**: Authorized clinic staff can identify the active registration and open any previous registration in no more than three interactions.
- **SC-004**: For patients with up to 10 registrations and 200 total reviews, the initial patient record becomes usable within 2 seconds under normal clinic conditions.
- **SC-005**: Reviews and registrations are presented in the required order in 100% of ordering tests, including records that share the same displayed date.
- **SC-006**: At least 90% of representative clinic users can locate a specified historical review and its wound images within 30 seconds during usability validation.
- **SC-007**: 100% of displayed events that contain both identities show the clinical employee and data-entry user separately.
- **SC-008**: Patients with zero registrations, one registration, no active registration, and missing optional clinical sections each receive a complete, understandable page state with no broken or misleading sections.

## Assumptions

- The patient identifier remains stable across registrations and is the authoritative link for assembling the longitudinal record.
- Existing business rules normally allow no more than one active registration per patient.
- Historical registrations are read-only from the aggregated patient page.
- Existing authorization rules for patient and registration records apply unchanged to the longitudinal record.
- Existing dedicated registration pages remain available for status-appropriate actions.
- Existing registration, diagnosis, review, appointment, wound-photo, employee, and data-entry records contain the relationships needed to associate each item with its owning registration.
- Review photos already identify their wound assessment, while initial baseline photos identify their wound and registration.
- The feature does not alter clinical retention rules or delete historical information.
