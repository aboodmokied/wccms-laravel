# Feature Specification: Clinical Terms in English

**Feature Branch**: `014-clinical-terms-english`

**Created**: 2026-10-19

**Status**: Draft

**Input**: User description: "Convert the monthly report table to English so it matches the clinic workbook, and the
registration form labels: these are medical terms and must stay in English. Wherever they are displayed, on any page,
show them in English; do not translate medical terms."

## Clarifications

### Session 2026-10-19

- Rule (user): medical terms are never translated. On every page — forms, summaries, timelines, patient record,
  reports and exports — a medical term is displayed in English. Administrative text (page titles, buttons, navigation,
  messages, administrative field labels such as department, nurse, follow-up date) stays Arabic.
- Q: Which form labels become English? → A: The medical field labels only; administrative labels stay Arabic.
- Q: How do assessment choice values (wound bed, exudate, margins, location…) get English names? → A: A new English
  label per choice, displayed everywhere; the Arabic label is kept; administrators edit both.
- Q: Are review outcomes and case outcomes medical terms? → A: Yes. Overall review outcomes, per-wound outcomes and
  the monthly report outcome column are displayed in English.
- The monthly report table and export follow the workbook: workbook column headers, `Male`/`Female`, month as
  `Nov -2025`, workbook sheet names.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Monthly report in the workbook's language (Priority: P1)

A clinic user opens the monthly case list, summary or readmissions, or exports them, and sees the same headers and
values as the clinic workbook.

**Why this priority**: The user asked for the report to match the example workbook.

**Independent Test**: Seed cases, open the case list and the export, and compare headers and values with the workbook.

**Acceptance Scenarios**:

1. **Given** the case list, **When** it renders, **Then** the headers are `NO`, `Pt Name`, `MONTHS`, `ID`, `Gender`,
   `DoB`, `Diagnosis`, `Discussion`, `DoA`, `PMH`, `Type of Wound`, `Type of DSG`, `Medication`, `Procedure`,
   `Department`, `Picture`, `DISCHARGE`, `Comment`, in that order.
2. **Given** a November 2025 case of a male patient, **When** it renders, **Then** the month reads `Nov -2025` and the
   gender `Male`.
3. **Given** a case closed by a death review, **When** it renders, **Then** its Comment reads `Death`.
4. **Given** an export, **When** it is opened, **Then** its sheets are named `CASES`, `CASES-A` and `ReAdmission`, with
   the same headers and values as the screen.
5. **Given** the summary page, **When** it renders, **Then** the gender groups read `Male` and `Female`, while the page
   title, filters and buttons stay Arabic.

---

### User Story 2 - Medical labels and values in the forms (Priority: P1)

A nurse accepting a registration, adding a wound, or recording a daily or scheduled review sees every medical field
label and every medical choice in English, and every administrative label in Arabic.

**Why this priority**: The forms are where clinicians read and choose the terms every day.

**Independent Test**: Open each form and check every medical label and choice against the glossary.

**Acceptance Scenarios**:

1. **Given** the registration acceptance form, **When** it renders, **Then** the medical labels read as in the
   glossary (for example `Exudate Amount`, `Wound Bed`, `Type of DSG`) and the administrative labels stay Arabic
   (for example `القسم`, `الممرض السريري`).
2. **Given** the wound-bed choices, **When** they render, **Then** they read `Granulation`, `Necrotic`, `Sloughing`,
   `Epithelializing`.
3. **Given** the daily review form, **When** it renders, **Then** the overall and per-wound outcome options are in
   English.
4. **Given** a required medical field left empty, **When** the form is submitted, **Then** the Arabic error message
   names the field by its English label (for example `حقل Exudate Amount مطلوب.`).

---

### User Story 3 - Medical terms in English wherever they are displayed (Priority: P2)

Any page that displays saved clinical data — registration details, review timeline, patient medical record, structured
assessment summary, latest-wound comparison, patient history report, dashboard activity — shows medical labels, choice
values and outcomes in English.

**Why this priority**: The rule applies to every page, but reading pages follow the forms in importance.

**Independent Test**: Seed a structured case with reviews and open every page that displays it.

**Acceptance Scenarios**:

1. **Given** a review closed as worsened-admission, **When** the timeline renders, **Then** it reads
   `Worsened – Admitted`.
2. **Given** a structured assessment, **When** its summary renders, **Then** labels and values are English
   (for example `Exudate Type: Purulent`).
3. **Given** a legacy record with free-text notes, **When** it renders, **Then** the notes are shown as written
   (stored text is never translated).

---

### User Story 4 - Administrators manage English choice names (Priority: P3)

An administrator edits a choice's English name (and Arabic name) on the assessment-choice screen.

**Why this priority**: Wording must stay editable without a code change.

**Independent Test**: Edit a choice's English name and see it on the forms.

**Acceptance Scenarios**:

1. **Given** the assessment-choice screen, **When** an administrator saves a new English name, **Then** every form and
   page shows it.
2. **Given** an English name left empty, **When** the administrator saves, **Then** the save is rejected with an Arabic
   message.

---

### Edge Cases

- A choice created before the English names exist has no English name: the migration's seeder fills every seeded
  choice; a choice an administrator added later falls back to its stable key turned into words until a name is entered.
- An outcome value without a glossary entry (unexpected data) shows its stored code rather than an empty cell.
- English terms inside the RTL layout display left-to-right without reordering surrounding Arabic text.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST keep one glossary of medical field labels, review outcomes, case outcomes and gender, in
  English, used by every page, form, validation message and export.
- **FR-002**: Every assessment choice MUST have an English name, displayed wherever the choice is displayed; the
  Arabic name is kept.
- **FR-003**: Administrators MUST be able to edit a choice's English and Arabic names; the English name is required
  and at most 150 characters.
- **FR-004**: The registration acceptance form, add-wound form, daily review form and scheduled review form MUST show
  medical labels and choices in English and administrative labels in Arabic.
- **FR-005**: Validation messages MUST stay Arabic and name medical fields by their English label.
- **FR-006**: Registration details, review timeline, patient medical record, assessment summary, latest-wound
  comparison, patient history report (screen and CSV) and dashboard activity MUST show medical terms in English.
- **FR-007**: The monthly report table and export MUST use the workbook headers, `Male`/`Female`, month as `Mon -YYYY`,
  English outcomes, and sheets `CASES`, `CASES-A`, `ReAdmission`.
- **FR-008**: Stored free text (notes, descriptions, observations) MUST be displayed as written.
- **FR-009**: English terms MUST render left-to-right inside the RTL layout.
- **FR-010**: The constitution MUST record the exception: medical terminology is displayed in English; all other
  user-facing text stays Arabic.

### Key Entities

- **Clinical glossary**: English display names for medical field labels, review outcomes, per-wound outcomes, case
  outcomes and gender.
- **Assessment choice**: gains an English name beside its Arabic name; its stable category and key do not change.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: The monthly report's 18 headers and its gender, month and outcome values match the workbook's wording.
- **SC-002**: On the four clinical forms and the seven reading pages, no medical label, choice or outcome is shown in
  Arabic (verified per page against the glossary).
- **SC-003**: 100% of seeded assessment choices have an English name after migration.
- **SC-004**: Every existing test that asserted an Arabic medical term passes with the English term, and no
  administrative Arabic text changes.

## Assumptions

- English names in [contracts/clinical-glossary.md](contracts/clinical-glossary.md) are proposals derived from the
  stable keys and the workbook's wording, for the user's review before implementation.
- `ReAdmission` corrects the workbook's spelling `ReAddmition`.
- The doctor specialty column stays deferred (spec 013).
- Master data names (diagnoses, wound types, dressings, medications, procedures, PMH) are already English and are not
  changed.
