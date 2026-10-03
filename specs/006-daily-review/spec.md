# Feature Specification: Phase 6 — Daily Review

**Feature Branch**: `006-daily-review`  
**Created**: 2026-09-29  
**Status**: Approved / In-Development  
**Input**: User description: "Phase 6 — Daily Review: Daily follow-up visit screen per registration showing all active wounds, documenting wound observations, dressing changes, medication updates, per-wound outcome assessments (improved+discharge / improved+follow-up / worsened / deceased), overall registration outcome assessment, auto-scheduling review appointments upon discharge decision, and converting static HTML pre-views into Metronic 8 Blade views."

---

## Clarifications & Architectural Alignment

### Domain Model & Schema Design Principles

1. **Dressing and Medication Location (DO NOT MOVE THEM)**:
   - `wc_reg_diagnosis_tb` represents initial wound diagnosis created during registration. It MUST NOT contain dressing or medication columns.
   - `wc_daily_review_tb` represents a single daily follow-up visit.
   - `wc_daily_review_detail_tb` represents the evaluation of an individual wound/diagnosis during a specific visit.
   - `WC_DREVD_DRESSING_CD` MUST remain in `wc_daily_review_detail_tb`.
   - `WC_DREVD_MEDICATION_CD` MUST remain in `wc_daily_review_detail_tb`.
   - Do NOT add `WC_RDIAG_DRESSING_CD` or `WC_RDIAG_MEDICATION_CD` to `wc_reg_diagnosis_tb`.
   - Do NOT require `dressing_code` or `medication_code` in Phase 5 initial wound diagnosis creation.
   - Current dressing and medication shown in the UI wound tabs MUST be derived from the latest applicable `wc_daily_review_detail_tb` record for that diagnosis.

2. **Entity Structure**:
   ```text
   wc_registration_tb
           │
           ├── wc_reg_diagnosis_tb
           │          │
           │          └── wc_daily_review_detail_tb
           │                    ├── dressing (WC_DREVD_DRESSING_CD)
           │                    ├── medication (WC_DREVD_MEDICATION_CD)
           │                    ├── observation (WC_DREVD_OBSERVATION)
           │                    └── wound outcome (WC_DREVD_OUTCOME_STATUS)
           │
           └── wc_daily_review_tb
                      ├── overall outcome (WC_DREV_OVERALL_STATUS)
                      ├── nurse (WC_DREV_NURSE_CD)
                      ├── entry user (WC_DREV_ENTRY_USER_CD)
                      ├── notes (WC_DREV_GENERAL_NOTES)
                      └── scheduled review (WC_DREV_SCHEDULED_REVIEW_CD)
   ```

3. **Visit-Level vs Per-Wound Outcomes**:
   - **Visit Header (`WC_DAILY_REVIEW_TB`)**: `WC_DREV_OVERALL_STATUS`
     - Allowed values: `IMPROVED_DISCHARGE`, `IMPROVED_SCHEDULED`, `WORSENED_STAY`, `DEATH`
     - Governs registration workflow and status transitions.
   - **Wound Detail (`WC_DREVD_DETAIL_TB`)**: `WC_DREVD_OUTCOME_STATUS`
     - Allowed values: `IMPROVED_DISCHARGE`, `IMPROVED_FOLLOWUP`, `WORSENED`, `DECEASED`
     - Records clinical progress for an individual wound only.

4. **Registration Discharge & Appointment Scheduling Logic**:
   - Status transition to `DISCHARGED` depends ONLY on visit-level `WC_DREV_OVERALL_STATUS`.
   - An individual wound outcome of `IMPROVED_DISCHARGE` does NOT by itself discharge the registration.
   - When `WC_DREV_OVERALL_STATUS` = `IMPROVED_DISCHARGE` or `IMPROVED_SCHEDULED`:
     - `WC_REG_STATUS = 'DISCHARGED'`
     - `WC_REG_DISCHARGED_AT = now()`
     - `WC_REG_DISCHARGED_BY_EMP_CD = WC_DREV_NURSE_CD`
   - When `WC_DREV_OVERALL_STATUS` = `IMPROVED_SCHEDULED`:
     - Create a pending record in `wc_scheduled_review_tb` inside the same database transaction.
     - Link created appointment via `WC_DREV_SCHEDULED_REVIEW_CD`.
   - When `WORSENED_STAY` or `DEATH`:
     - Do NOT automatically apply the discharge workflow.

---

## User Scenarios & Testing

### User Story 1 - Daily Review History & Wound Overview (Priority: P1) 🎯 MVP

As a clinic nurse, I want to view a patient's active registration details alongside all recorded wound diagnoses and past daily review entries on a single screen, so that I can evaluate wound healing progress before recording a new evaluation.

**Why this priority**: Core clinical entry point for daily patient assessment.

**Independent Test**: Navigate to `/registrations/{registration}/daily-reviews` for an active registration. Verify patient summary, active wound tabs (displaying current dressing/medication derived from latest daily review detail), and past daily review history timeline render accurately.

**Acceptance Scenarios**:
1. **Given** an authenticated clinic nurse viewing `/registrations/{registration}/daily-reviews`, **When** page loads, **Then** display read-only patient summary card and current registration status (`ACTIVE` or `DISCHARGED`).
2. **Given** a registration with active wound diagnoses, **When** viewing wound tabs, **Then** render tabs for each diagnosis displaying latest applied dressing and medication derived from `wc_daily_review_detail_tb`.
3. **Given** existing daily review records, **When** viewing timeline, **Then** render visit cards ordered newest first displaying visit date, attending nurse, data entry user, overall status badge, and per-wound evaluations.

---

### User Story 2 - Multi-Wound Daily Follow-Up Entry (Priority: P1) 🎯 MVP

As a clinic nurse, I want to record today's follow-up visit for a patient by documenting observations, updated dressings, updated medications, and outcome assessments for each active wound, as well as an overall visit-level outcome.

**Why this priority**: Essential clinical functionality to capture daily treatment changes.

**Independent Test**: Open "+ Add Daily Review" modal, select attending nurse, select overall visit outcome, enter per-wound observations, dressings, medications, and per-wound outcomes for all active wounds, then submit. Verify `WC_DAILY_REVIEW_TB` and `WC_DREVD_DETAIL_TB` records are populated.

**Acceptance Scenarios**:
1. **Given** opening daily review form, **When** form renders, **Then** display separated sections for Overall Visit Outcome and Per-Wound Evaluation for each active wound.
2. **Given** completing wound detail entries, **When** entering per-wound info, **Then** allow selecting observation text, updated dressing (`WC_DRESSING_TB`), updated medication (`WC_MEDICATION_TB`), and per-wound outcome status (`IMPROVED_DISCHARGE`, `IMPROVED_FOLLOWUP`, `WORSENED`, `DECEASED`).
3. **Given** selecting overall visit outcome (`WC_DREV_OVERALL_STATUS`), **When** choosing from `IMPROVED_DISCHARGE`, `IMPROVED_SCHEDULED`, `WORSENED_STAY`, or `DEATH`, **Then** require selecting attending nurse (`PER_EMPLOYEE_TB`) and save dual audit fields (`WC_DREV_NURSE_CD` vs `WC_DREV_ENTRY_USER_CD` = `Auth::user()->PER_USER_CODE`).

---

### User Story 3 - Automated Follow-Up Appointment Scheduling on Discharge (Priority: P2)

As a clinic supervisor or nurse, when an overall visit evaluation outcome of `IMPROVED_SCHEDULED` is selected during daily review, I want the system to automatically transition registration status to `DISCHARGED` and generate a scheduled review appointment awaiting patient return.

**Why this priority**: Ensures continuity of care for outpatient follow-up.

**Independent Test**: Record a daily review with overall outcome `IMPROVED_SCHEDULED`. Verify registration status becomes `DISCHARGED` and a pending `WC_SCHEDULED_REVIEW_TB` record is created inside the same transaction and linked via `WC_DREV_SCHEDULED_REVIEW_CD`.

**Acceptance Scenarios**:
1. **Given** daily review submitted with overall outcome `IMPROVED_SCHEDULED` or `IMPROVED_DISCHARGE`, **When** transaction completes, **Then** set `WC_REG_STATUS = DISCHARGED`, `WC_REG_DISCHARGED_AT = NOW()`, and `WC_REG_DISCHARGED_BY_EMP_CD = WC_DREV_NURSE_CD`.
2. **Given** overall outcome `IMPROVED_SCHEDULED`, **When** transaction completes, **Then** create pending `WC_SCHEDULED_REVIEW_TB` record and update `WC_DREV_SCHEDULED_REVIEW_CD`.

---

### User Story 4 - Discharged Registration Review Lock (Priority: P3)

As a clinic staff member, I want the system to disable creating new daily reviews on registrations marked as `DISCHARGED`, while keeping historical daily review records accessible for auditing.

**Why this priority**: Prevents unauthorized modifications on discharged clinical files.

**Independent Test**: View `/registrations/{registration}/daily-reviews` for a `DISCHARGED` registration. Verify "+ Add Daily Review" button is hidden/disabled, status alert banner is rendered, and POST request returns HTTP 403.

---

## Requirements

### Functional Requirements

- **FR-001**: System MUST provide a daily review workspace (`/registrations/{registration}/daily-reviews`) rendering patient summary, active wound tabs, and review history timeline.
- **FR-002**: System MUST store daily review header in `WC_DAILY_REVIEW_TB` with visit date, attending nurse (`WC_DREV_NURSE_CD`), logged-in user (`WC_DREV_ENTRY_USER_CD`), general notes, and overall status (`WC_DREV_OVERALL_STATUS`).
- **FR-003**: System MUST store per-wound evaluations in `WC_DREVD_DETAIL_TB` including dressing (`WC_DREVD_DRESSING_CD`), medication (`WC_DREVD_MEDICATION_CD`), observation (`WC_DREVD_OBSERVATION`), and per-wound outcome status (`WC_DREVD_OUTCOME_STATUS`).
- **FR-004**: System MUST derive current dressing and medication shown in wound tabs from the latest applicable `WC_DREVD_DETAIL_TB` record.
- **FR-005**: System MUST require selecting overall outcome (`WC_DREV_OVERALL_STATUS`: `IMPROVED_DISCHARGE`, `IMPROVED_SCHEDULED`, `WORSENED_STAY`, `DEATH`).
- **FR-006**: System MUST automatically transition registration status to `DISCHARGED` upon saving overall outcome `IMPROVED_DISCHARGE` or `IMPROVED_SCHEDULED`.
- **FR-007**: System MUST automatically generate a pending scheduled review (`WC_SCHEDULED_REVIEW_TB`) inside the same transaction when overall outcome is `IMPROVED_SCHEDULED`.
- **FR-008**: System MUST populate `WC_DREV_ENTRY_USER_CD` from `Auth::user()->PER_USER_CODE` and NOT accept it from the client request.
- **FR-009**: System MUST prevent creating new daily reviews on `DISCHARGED` registrations.
- **FR-010**: All UI components MUST render in Arabic with native RTL layout.

---

## Success Criteria

- **SC-001**: Nurses can complete and save a multi-wound daily review for a patient with 2 wounds in under 90 seconds.
- **SC-002**: 100% of recorded daily reviews maintain strict audit separation between attending clinical nurse and data entry user.
- **SC-003**: 100% of daily reviews saved with `IMPROVED_SCHEDULED` outcome trigger automatic creation of a pending scheduled review record and transition registration status to `DISCHARGED`.
- **SC-004**: 100% of discharged registrations block new daily review creation while preserving read-only access.
