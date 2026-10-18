# Feature Specification: Phase 5B — Structured Wound Assessment & Hospital Form Parity

**Feature Branch**: `012-structured-wound-assessment`

**Created**: 2026-10-10

**Status**: Draft

**Input**: User description: "Phase 5B — Structured Wound Assessment & Hospital Form Parity: capture the diagnosis-specific clinical data found in the hospital's initial wound assessment and daily follow-up forms during initial registration, daily reviews, and fulfilled scheduled reviews."

## Clarifications

### Session 2026-10-12

- Q: When must the initial assessment for every diagnosis be considered complete? → A: The request cannot be accepted and the registration cannot be created until every included diagnosis has a complete initial assessment.
- Q: What measurement rule applies to wound length, width, and depth? → A: Length and width must be greater than zero; depth may be zero; each value is limited to 1000 mm and two decimal places.
- Q: How are scheduled appointments created when wounds would otherwise have different follow-up plans? → A: The registration uses one shared follow-up date and objective for all wounds, and creates one scheduled appointment from that shared plan.
- Q: When does a diagnosis or wound stop being active and no longer require assessment at every review? → A: Every diagnosis remains active and mandatory for every review until the entire registration is discharged.
- Q: How are shared allergies and comorbidities recorded during the initial assessment? → A: Record allergies as yes/no with required free text when yes; record comorbidities as a multi-select from the existing PMH list or the mutually exclusive “None” choice, once per registration.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Record a Complete Initial Assessment for Every Wound (Priority: P1)

As a Wound Care Team clinician accepting a patient, I want to record the hospital's complete initial wound assessment separately for every diagnosis, so that each wound begins with an accurate and comparable clinical baseline.

**Why this priority**: The initial assessment establishes the identity and baseline state of every wound. Follow-up documentation cannot be clinically reliable when the baseline fields are incomplete or shared between diagnoses.

**Independent Test**: Accept a patient with three diagnoses, complete a different initial assessment and baseline photo for each diagnosis, then reopen the registration and verify that all three assessments retain only their own findings.

**Acceptance Scenarios**:

1. **Given** an acceptance workflow containing multiple diagnoses, **When** the clinician records the initial assessment, **Then** patient and encounter information appears once and a separate assessment section appears for every diagnosis.
2. **Given** three diagnosis sections, **When** the clinician enters different wound findings, measurements, treatments, and photos in each section, **Then** each diagnosis retains its own values without copying or overwriting another diagnosis.
3. **Given** an initial assessment, **When** length, width, and depth are recorded, **Then** each dimension is stored as a separate numeric millimetre value and is displayed with its unit.
4. **Given** a shared next-visit date and objective, **When** all initial assessments are completed, **Then** one follow-up plan remains attached to the registration and its planned visit is available in the scheduled-review workflow for all included wounds.
5. **Given** incomplete or inconsistent clinical findings, **When** the clinician attempts to complete the initial assessment, **Then** the system identifies the affected diagnosis and explains the correction in Arabic without discarding valid entries for other diagnoses.
6. **Given** any included diagnosis has an incomplete initial assessment, **When** the clinician attempts to accept the request, **Then** the request remains unaccepted and no registration is created.
7. **Given** allergies and comorbidities apply to the patient encounter, **When** the clinician completes the initial workflow, **Then** those facts are entered once for the registration and shown consistently with every diagnosis.

---

### User Story 2 - Document a Structured Daily Follow-Up Per Wound (Priority: P1)

As a Wound Care Team clinician conducting a daily follow-up, I want to evaluate each wound using the hospital's structured follow-up fields, so that changes in wound status can be compared reliably over time.

**Why this priority**: Daily clinical decisions depend on consistent observations of wound status, tissue, exudate, odor, pain, treatment, and response for the same wound.

**Independent Test**: Open a registration with two diagnoses, submit one daily visit containing different structured values and photos for both wounds, and verify that the visit has one shared header with two isolated wound assessments.

**Acceptance Scenarios**:

1. **Given** a registration with multiple active diagnoses, **When** a daily review is started, **Then** the visit date, time, clinician, and general notes are captured once while clinical findings are entered per diagnosis.
2. **Given** a diagnosis with an earlier assessment, **When** its daily follow-up panel is opened, **Then** the clinician can see the latest comparable findings while entering the new assessment.
3. **Given** different wound-bed, exudate, odor, pain, dressing, medication, status, and photo values for two diagnoses, **When** the review is saved, **Then** every value remains attached to the diagnosis where it was entered.
4. **Given** a dry wound, **When** an incompatible exudate type is selected, **Then** the system requires the clinician to resolve the inconsistency before completing the review.
5. **Given** a daily review saved successfully, **When** it is viewed in the registration and longitudinal history, **Then** all structured findings and audit identities are readable under the owning wound and visit.

---

### User Story 3 - Complete a Scheduled Review with the Same Clinical Dataset (Priority: P1)

As a clinician handling a scheduled appointment, I want to use the same wound-assessment workflow as a daily review, so that scheduled and unscheduled follow-ups produce comparable clinical records.

**Why this priority**: A scheduled visit is a clinical follow-up, not a different type of wound assessment. Using different data sets would create gaps in the patient's treatment history.

**Independent Test**: Start a pending scheduled appointment, complete structured assessments for its registration diagnoses, and verify that the appointment becomes completed and the resulting visit is comparable with daily reviews.

**Acceptance Scenarios**:

1. **Given** a pending scheduled appointment, **When** the clinician starts its visit, **Then** the same diagnosis-specific assessment fields available in a daily review are presented.
2. **Given** a completed scheduled assessment, **When** the history is viewed, **Then** the clinical visit is linked to the appointment and is clearly identified as scheduled.
3. **Given** a scheduled appointment belonging to an older registration, **When** a clinician attempts to assess a diagnosis from another registration, **Then** the system rejects the mismatch.
4. **Given** a missed, cancelled, or voided appointment, **When** it is opened, **Then** no completed clinical assessment can be recorded for it.

---

### User Story 4 - Compare Wound Progress with Complete Accountability (Priority: P2)

As an authorized clinical user, I want to compare a wound's baseline and follow-up findings and see who performed and entered each assessment, so that I can understand its progress and trust the record.

**Why this priority**: Structured capture creates value only when staff can read changes over time and attribute every assessment to the correct people.

**Independent Test**: Open a wound with an initial assessment, a daily review, and a scheduled review performed by one employee and entered by another user; verify the chronological comparison and both identities for every event.

**Acceptance Scenarios**:

1. **Given** a wound with multiple assessments, **When** its history is opened, **Then** baseline and follow-up values are shown chronologically for that wound only.
2. **Given** measurements across multiple visits, **When** the history is reviewed, **Then** length, width, and depth retain their numeric values and units for comparison.
3. **Given** an assessment whose clinical employee differs from its data-entry user, **When** the assessment is displayed, **Then** both identities and the assessment timestamp are shown separately.
4. **Given** a legacy assessment without the new structured values, **When** it is displayed, **Then** its existing narrative, treatment, outcome, and photos remain readable and absent structured values are labeled as not recorded.

---

### User Story 5 - Maintain Clinically Approved Assessment Choices (Priority: P3)

As an authorized administrator, I want the wound-assessment choices to use stable clinical meanings and Arabic labels, so that staff document the same finding consistently and historical records remain understandable.

**Why this priority**: Consistent terminology enables reliable documentation and reporting, but initial and follow-up capture can operate first with an approved baseline vocabulary.

**Independent Test**: Deactivate an assessment choice after it has been used, verify that it is unavailable for new assessments, and confirm that historical assessments still show its label.

**Acceptance Scenarios**:

1. **Given** an active approved clinical choice, **When** a clinician records an assessment, **Then** the choice is available with a clear Arabic label.
2. **Given** an inactive clinical choice, **When** a new assessment is entered, **Then** the choice is unavailable for selection.
3. **Given** a historical assessment using a now-inactive choice, **When** the assessment is viewed, **Then** the recorded value remains visible and unchanged.

### Edge Cases

- A registration has no diagnoses: the clinical assessment cannot start and the user is directed to record at least one diagnosis first.
- A registration has many diagnoses: each diagnosis remains identifiable and must be assessed before every visit is completed until the registration is discharged, while patient or encounter information is not repeated.
- Two diagnoses use the same diagnosis or wound-type label: they remain separate wound records with separate assessments and photos.
- A wound has more than one simultaneous tissue or margin finding: all clinically allowed findings remain available without one selection erasing another, and pocketing is recorded under wound-margin findings.
- A wound length or width is zero or negative, any dimension is negative or greater than 1000 mm, or a value has more than two decimal places: the affected field is rejected with a diagnosis-specific message; a zero depth remains valid for a superficial wound.
- A wound is marked dry/no exudate while an exudate type is selected: the conflicting values must be resolved.
- Allergies are marked present without a description, or “None” is selected together with one or more PMH conditions: the conflicting registration-level values must be resolved before acceptance.
- A follow-up plan has a date in the past: the user receives a clear validation message before completion.
- A scheduled appointment is fulfilled twice because of a repeated submission: only one completed clinical visit is retained.
- A diagnosis belongs to another patient or registration: it cannot be included in the assessment.
- A photo is uploaded under one diagnosis and then another diagnosis is selected: the photo remains attached to its original diagnosis unless explicitly removed before submission.
- A historical vocabulary choice is no longer active: it remains visible in history but cannot be chosen for a new assessment.
- An older record has narrative observations but no structured assessment: the narrative remains the source record and no structured values are inferred automatically.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST capture patient and encounter context once per initial registration or follow-up visit and MUST capture clinical assessment findings separately for each diagnosis/wound.
- **FR-002**: The system MUST support one or more diagnosis-specific initial assessments in the request-acceptance workflow that creates the patient registration.
- **FR-003**: Each included diagnosis MUST have a complete initial assessment containing wound type, diagnosis, exudate amount, exudate type when applicable, odor, pain, fever, wound-bed findings, wound-margin findings, dimensions, anatomical location, swab status, nutrition status, wound status, treatment, and available baseline photos before the request can be accepted or the registration created. The initial workflow MUST also contain the single registration-level follow-up plan defined by FR-011; that plan MUST NOT be duplicated inside diagnosis assessments.
- **FR-004**: The initial workflow MUST record allergies once per registration as a yes/no value and MUST require a free-text description when allergies are present; it MUST record comorbidities once per registration as a multi-select from the existing PMH list or the mutually exclusive “None” choice, without duplicating either fact per diagnosis.
- **FR-005**: The system MUST record wound length, width, and depth as separate numeric measurements in millimetres with at most two decimal places; length and width MUST be greater than zero, depth MUST be zero or greater, and no dimension may exceed 1000 mm.
- **FR-006**: The system MUST support a primary anatomical location, laterality when applicable, and optional precise location notes for each wound.
- **FR-007**: The system MUST allow multiple simultaneous wound-bed findings and multiple simultaneous wound-margin findings; `Pocketing` MUST be classified and presented as a wound-margin finding.
- **FR-008**: Pain documentation MUST distinguish pain severity from whether pain occurs during dressing changes.
- **FR-009**: A finding of dry/no exudate MUST NOT coexist with an exudate type unless an approved clinical exception is defined.
- **FR-010**: Treatment documentation MUST distinguish the dressing used, medication used, and a performed procedure such as debridement.
- **FR-011**: The system MUST allow a clinician to record one shared next-visit date or frequency and one shared main objective for all wounds in the registration; diagnosis-specific follow-up dates and objectives MUST NOT be created.
- **FR-012**: When the shared next-visit date is recorded, one planned visit for the registration MUST be available in the scheduled-review workflow without requiring the patient or diagnosis context to be entered again.
- **FR-013**: A daily follow-up MUST contain one shared visit context and one separate assessment for each included diagnosis/wound.
- **FR-014**: Each follow-up assessment MUST include assessment date/time, wound status, wound-bed findings, exudate amount and type, odor, pain, dressing/procedure, medication, clinical observation, change since the previous assessment, outcome, follow-up requirement, and available review photos. The outcome MUST have one authoritative stored value in the assessment's required linked review-detail row and MUST NOT be duplicated in the canonical assessment row.
- **FR-015**: Before entering a follow-up assessment, users MUST be able to see the most recent comparable assessment for the same diagnosis without seeing values from another diagnosis.
- **FR-016**: A daily or scheduled visit MUST include a completed assessment for every diagnosis in the registration before the visit can be completed; all diagnoses remain active until the registration is discharged.
- **FR-017**: When any diagnosis in the registration lacks an assessment, the system MUST prevent visit completion and identify each incomplete diagnosis without discarding completed diagnosis assessments.
- **FR-018**: A fulfilled scheduled appointment MUST offer the same diagnosis-specific clinical dataset and validation behavior as a daily review.
- **FR-019**: A completed scheduled assessment MUST remain linked to its scheduled appointment and MUST appear as a clinical visit in the registration history.
- **FR-020**: Missed, cancelled, and voided appointments MUST NOT accept a completed wound assessment.
- **FR-021**: The nurse MUST be recorded as the primary clinical actor for the initial assessment; the Wound Care Team participant and doctor MUST be recordable as optional clinical participants, while the authenticated data-entry identity remains separate.
- **FR-022**: Every assessment MUST record the clinical employee responsible for the assessment separately from the authenticated user who entered the data.
- **FR-023**: The clinical employee identity and completion timestamp MUST provide an electronic attestation for the assessment unless hospital policy later requires a separate signature artifact.
- **FR-024**: Baseline photos MUST remain attached to their initial diagnosis assessment, and follow-up photos MUST remain attached to the diagnosis and visit where they were recorded. A photo file MUST exist at its immutable final path before its metadata is committed; storage failure MUST prevent the clinical procedure call, and procedure failure MUST remove files prewritten for the failed aggregate.
- **FR-025**: The system MUST prevent any assessment, finding, treatment, appointment, or photo from being attached to a diagnosis belonging to another registration or patient.
- **FR-026**: The system MUST reject repeated completion of the same visit while preserving the first successful clinical record.
- **FR-027**: The system MUST display the structured assessment chronologically under its owning diagnosis in registration and longitudinal history views.
- **FR-028**: The system MUST preserve existing narrative observations, wound changes, dressing, medication, outcomes, photos, and audit identities.
- **FR-029**: Existing records without the new structured values MUST remain readable and MUST display those absent values as not recorded rather than deriving them from narrative text.
- **FR-030**: Clinical choices MUST use stable meanings and Arabic display labels for wound status, tissue findings, wound margins, exudate, odor, pain, fever, nutrition, location, dressing, and procedure options.
- **FR-031**: A clinical choice that is retired MUST be unavailable for new assessments while remaining readable in historical assessments.
- **FR-032**: Validation errors MUST identify the affected diagnosis and field in Arabic and MUST preserve valid entries already made elsewhere in the form.
- **FR-033**: All new assessment entry and history interfaces MUST support Arabic and right-to-left use on desktop and narrow screens.
- **FR-034**: Existing authorization rules for registration, daily review, scheduled review, and patient history MUST apply to the expanded assessment dataset.
- **FR-035**: Every clinically meaningful field in `First Visit.xlsx` and `wound care daily follow up.docx` MUST have one documented owner, meaning, allowed-value definition, and entry/display location before implementation is accepted.

### Key Entities

- **Patient**: The person receiving care; supplies stable identity, date of birth, gender, and other shared demographics.
- **Registration**: One episode of wound-care service; supplies department, date, status, allergy presence and description, selected PMH comorbidities or “None,” and the set of diagnosis/wound records.
- **Diagnosis / Wound**: A distinct wound identity within one registration. Every baseline and follow-up assessment belongs to exactly one diagnosis/wound, and the wound remains active until the entire registration is discharged.
- **Wound Assessment**: The structured clinical state of one wound at a specific point in time, either its initial baseline or a follow-up assessment.
- **Assessment Finding**: One selectable clinical observation within a category such as wound bed or wound margin; multiple findings may be associated with one assessment when clinically allowed.
- **Clinical Visit**: The shared context for a daily or scheduled follow-up, including date/time, overall result, notes, and the set of diagnosis-specific assessments.
- **Scheduled Appointment**: A planned follow-up that may produce one clinical visit when fulfilled.
- **Treatment**: The dressing, medication, procedure, and narrative plan applied during an assessment.
- **Follow-Up Plan**: The registration-level next-visit timing or frequency and clinical objective shared by all wounds in that registration.
- **Wound Photo**: A baseline or follow-up image belonging to one diagnosis and, for follow-up images, one clinical visit assessment.
- **Clinical Employee**: The hospital employee responsible for performing or attesting to the clinical assessment.
- **Data-Entry User**: The authenticated user who records the assessment in the system.
- **Clinical Choice**: An approved, stable wound-assessment option with a user-facing Arabic label and active/inactive lifecycle.

## Scope Boundaries

### Included

- Structured initial assessment fields from the supplied hospital first-visit form.
- Structured daily follow-up fields from both supplied follow-up forms.
- Multiple diagnosis/wound assessments within one registration or visit.
- The same clinical dataset for daily reviews and fulfilled scheduled reviews.
- Baseline and follow-up photos, treatment, follow-up planning, audit identities, validation, and longitudinal display.
- Preservation and readable presentation of legacy records that lack the new structured fields.
- Approved assessment vocabulary and historical readability of retired choices.

### Excluded

- Exact pixel-for-pixel printing or export of the supplied Excel and Word layouts.
- Automatic interpretation of narrative text into structured clinical findings.
- Editing or rewriting historical clinical records during migration.
- Hospital-wide patient, admission, allergy, doctor, or ward synchronization beyond data already available to this application.
- Laboratory processing or result tracking for a wound swab; this phase records only whether a swab was taken.
- Automated diagnosis, treatment recommendations, risk scoring, or clinical decision support.
- Merging separate diagnoses or transferring assessments between wounds or registrations.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of clinically meaningful fields in the two supplied hospital forms have an approved data owner, allowed-value definition, and visible entry/display location.
- **SC-002**: In validation scenarios with three diagnoses, 100% of initial and follow-up findings, measurements, treatments, and photos remain attached to the diagnosis where they were entered, with zero cross-diagnosis leakage.
- **SC-003**: At least 90% of representative clinical users can complete an initial registration containing three diagnosis assessments without re-entering patient or encounter information and without assistance.
- **SC-004**: At least 90% of representative clinical users can complete a follow-up visit for three wounds in under 8 minutes using the structured workflow.
- **SC-005**: Daily and fulfilled scheduled reviews achieve 100% parity for the required diagnosis-specific clinical fields.
- **SC-006**: 100% of invalid measurement, ownership, dry/exudate, appointment-state, and duplicate-submission scenarios are rejected with an understandable diagnosis-specific message.
- **SC-007**: Authorized users can locate the latest status, dimensions, treatment, and photo for a specified diagnosis within 30 seconds in at least 90% of usability trials.
- **SC-008**: 100% of new assessments display the clinical employee, data-entry user, and assessment time as separate audit facts.
- **SC-009**: 100% of sampled legacy registrations and reviews remain readable after the expanded assessment dataset is introduced.
- **SC-010**: In Arabic desktop and narrow-screen validation, every diagnosis section and validation message remains readable and attributable to the correct wound.

## Assumptions

- The supplied Excel and Word documents are approved workflow references and contain no patient-specific data requiring import.
- Each physical assessment sheet represents one diagnosis/wound, while patient and encounter context is shared across sheets.
- Millimetres are the canonical unit for length, width, and depth; values support at most two decimal places and a maximum of 1000 mm, with positive length and width and nonnegative depth.
- Pain is represented as a severity plus a separate indicator for pain during dressing changes.
- A wound has one primary anatomical location, optional laterality, and optional precise location notes unless clinical clarification changes this rule.
- Allergies and comorbidities are shared registration context: allergies use yes/no plus required description when present, while comorbidities use the existing PMH list or the mutually exclusive “None” choice; both are shown without duplication per wound.
- Debridement is treated as a performed procedure and not solely as a dressing choice.
- The nurse is the primary clinical actor; Wound Care Team and doctor participants are optional, and the authenticated data-entry user remains separately attributable.
- The responsible nurse identity and completion timestamp serve as electronic attestation; handwritten signature capture is outside current scope.
- Exact replicas of the source forms are not required; clinically complete structured capture and a readable equivalent history are sufficient.
- The current scheduled-review lifecycle remains authoritative: one registration-level follow-up plan applies to all wounds, and its scheduled appointment produces a clinical visit only when fulfilled.
- Every diagnosis added to a registration remains active and mandatory in each review until the entire registration is discharged; wounds do not have an independent healed or closed lifecycle in this phase.
- Existing wound type, diagnosis, dressing, medication, procedure, photo, authorization, and longitudinal-history capabilities remain available and are extended rather than replaced.

## Dependencies

- Patient, registration, diagnosis/wound, daily review, scheduled review, photo, employee, and data-entry identities already exist and remain stable.
- The hospital or clinical owner must approve the final Arabic labels and stable meanings for the clinical vocabulary before implementation is accepted.
- Phase 5A longitudinal history remains the destination for reading structured wound progress across registrations.
