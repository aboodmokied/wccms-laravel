# Feature Specification: Monthly Case Reports

**Feature Branch**: `013-monthly-case-reports`

**Created**: 2026-10-19

**Status**: Draft

**Input**: User description: "Add monthly reports inside the system that give a result equivalent to the clinic's Excel workbook (`نسخة للمهندسين .xlsx`): the monthly case list, the case summary by month, gender and diagnosis, the building (department group) view, and the readmission list."

## Source Workbook

The clinic currently keeps its monthly reporting in a spreadsheet. Its sheets define the expected output of this feature:

| Sheet | What it shows | Covered by |
|---|---|---|
| `CASES` | One row per case with 19 columns (listed in FR-004). 204 cases: 125 in Nov 2025, 79 in Dec 2025. | Data source for every report |
| `تقرير شهر` | `CASES` filtered to one month | User Story 1 |
| `CASES-A` | Case count per month (bar chart), by gender (pie chart), by diagnosis | User Story 2 |
| `مبنى الولادة الغربي` | `CASES` filtered to the departments of one building (Orthopedic Male/Female, Special Male/Female) | Department filter, FR-003 |
| `ReAddmition` | Every row whose patient ID already appeared in an earlier row | User Story 3 |
| `Tables` | The approved value lists (diagnoses, wound types, dressings, medications, procedures, departments, PMH, doctor specialties) | User Story 5 |

The workbook contains identifiable patient data. It is analysed locally and is never committed to the repository; tests use synthetic data (see Assumptions).

## Clarifications

### Session 2026-10-19

- Q: Is a report row one case or one wound? → A: One row per registration (case). Wound-level columns list the values of all the case's wounds, without duplicates.
- Q: Which date assigns a case to a month? → A: The clinic registration date (the date the registration request was accepted). The hospital admission date (DoA) is displayed but does not drive the month; in the workbook, 12 November cases were admitted to hospital in October.
- Q: Which dressing, medication and procedure values appear on a case row? → A: Every distinct value used during the case: the initial assessment, every follow-up assessment and review detail, and the registration procedure. A swab taken in any assessment appears as the procedure `swap culture`, as in the workbook.
- Q: Where does the doctor specialty (`DR` column) come from? → A: Out of scope for now; the column is omitted and will be added later as a separate feature (it needs a new specialty list and a new field at registration).
- Q: How is the building sheet reproduced? → A: With a multi-select department filter on every report; no building entity is added.
- Q: What about master data? → A: A seeder loads the workbook's `Tables` lists. Tissue states that the workbook lists under "Type of Wound" (Sloughing, Necrotic, Granulation, black tissue, Pocketing) are not wound types: they stay as structured-assessment wound-bed and wound-margin choices. The report's wound-type column shows the wound type followed by the tissue findings of the initial assessment.
- Q: Export format? → A: An Excel (`.xlsx`) file with a sheet per report.
- Q: Who may view and export? → A: Every authenticated user, as with the existing patient history report. Every export is recorded.
- Q: What counts as a readmission? → A: Any registration of a patient who already had an earlier registration, at any time — the workbook's formula. The first registration is never listed; each readmission row shows the previous registration's date and outcome.
- Q: What period does the summary cover? → A: A month range (from–to), defaulting to the last 12 months. The case list always covers a single month.
- Q: How is the outcome (`Comment`) column written? → A: Four values: `يستمر العلاج` (registration still active), `خروج` (last review outcome improved-discharge, or a manual discharge), `وفاة` (last review outcome death), `تحويل للمستشفى` (last review outcome worsened-admission).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Monthly case list (Priority: P1)

A clinic user opens the monthly report, chooses a month and optionally one or more departments, and sees every case registered in the clinic in that month, one row per case, with the same columns as the workbook's `CASES` sheet (except the doctor specialty).

**Why this priority**: This is the sheet the clinic produces every month; it replaces manual copying into the spreadsheet.

**Independent Test**: Seed cases across two months and several departments, open the report for one month, and verify that exactly that month's cases appear with the expected values in every column.

**Acceptance Scenarios**:

1. **Given** 125 cases registered in November 2025 and 79 in December 2025, **When** the user selects November 2025, **Then** 125 rows appear, numbered 1–125, ordered by clinic registration date.
2. **Given** a case with two wounds (diabetic foot and pressure ulcer), **When** it appears in the list, **Then** it occupies one row and its diagnosis cell shows both diagnoses.
3. **Given** a case whose dressing changed from gauze to hydrogel during treatment, **When** it appears in the list, **Then** its dressing cell shows both, without duplicates.
4. **Given** the user selects the departments Orthopedic Male, Orthopedic Female, Special Male and Special Female, **When** the list refreshes, **Then** only cases registered in those departments appear (the building view).
5. **Given** a case still under treatment, **When** it appears in the list, **Then** its discharge date is empty and its outcome reads `يستمر العلاج`.
6. **Given** a month with no cases, **When** the user selects it, **Then** an empty-state message appears instead of a table.

---

### User Story 2 - Case summary over a month range (Priority: P2)

A user chooses a month range (and optionally departments) and sees the case count per month as a bar chart, the gender split as a pie chart, and the case count per diagnosis — the workbook's `CASES-A` sheet.

**Why this priority**: Management compares months and case mix; it is the second most used part of the workbook.

**Independent Test**: Seed the synthetic dataset, select November–December 2025, and verify every count against the expected figures.

**Acceptance Scenarios**:

1. **Given** the synthetic dataset, **When** the user selects November 2025 to December 2025, **Then** the summary shows 125 and 79 cases per month, 82 female and 122 male, and diagnosis counts of 51 bedsore PEU, 11 Burn, 69 DF, 55 Inf Wound, 7 Row Area and 11 SSI, with a total of 204.
2. **Given** a case with two diagnoses, **When** the diagnosis counts are computed, **Then** the case is counted once under each of its diagnoses, while the per-month and gender totals count it once.
3. **Given** no range is chosen, **When** the summary opens, **Then** it covers the last 12 months including the current month.
4. **Given** the selected range contains months with no cases, **When** the bar chart renders, **Then** those months appear with a value of zero.

---

### User Story 3 - Readmissions (Priority: P2)

A user sees the cases in the selected month (or range) whose patient had an earlier registration, together with that earlier registration's date and outcome — the workbook's `ReAddmition` sheet.

**Why this priority**: Readmission is a quality signal the clinic tracks by hand today.

**Independent Test**: Seed a patient with registrations in November and December, open the readmission list for December, and verify only the December registration appears with the November date and outcome.

**Acceptance Scenarios**:

1. **Given** a patient registered on 22 Nov 2025 and again on 1 Dec 2025, **When** the user views readmissions for December 2025, **Then** the 1 Dec registration appears with the previous registration date 22 Nov 2025 and its outcome.
2. **Given** the same patient, **When** the user views readmissions for November 2025, **Then** the patient does not appear (a first registration is never a readmission).
3. **Given** a patient whose previous registration was a year earlier, **When** the new registration falls in the selected period, **Then** it is listed (no time window applies).
4. **Given** the synthetic dataset, **When** the user views readmissions for November–December 2025, **Then** 6 rows appear.

---

### User Story 4 - Excel export (Priority: P3)

From any report screen, the user downloads an `.xlsx` file containing the case list, the summary and the readmissions for the same filters.

**Why this priority**: The clinic shares the report as a spreadsheet; export removes manual re-entry, but the on-screen reports deliver value first.

**Independent Test**: Export November 2025 and open the file: three sheets, Arabic text readable, dates as dates, values identical to the screen.

**Acceptance Scenarios**:

1. **Given** the November 2025 report with two departments selected, **When** the user exports, **Then** the file contains a case-list sheet, a summary sheet and a readmission sheet, all filtered identically to the screen.
2. **Given** an export, **When** it completes, **Then** the system records who exported, when, the month or range, the departments and the number of rows.
3. **Given** Arabic patient names, **When** the file is opened in Excel, **Then** the names display correctly and the sheets read right-to-left.

---

### User Story 5 - Clinic value lists (Priority: P3)

The diagnoses, wound types, dressings, medications, procedures, departments and past-medical-history items used by the clinic are available in the system with the same names as the workbook, so reports read like the workbook.

**Why this priority**: Without the real lists the report is correct but its labels differ from the workbook; it does not block the reports themselves.

**Independent Test**: Run the seeder on a database that already has data and verify that every workbook value exists exactly once and no existing value was removed or duplicated.

**Acceptance Scenarios**:

1. **Given** a database without the workbook values, **When** the seeder runs, **Then** every value from the `Tables` sheet (except doctor specialties) exists and is active.
2. **Given** the seeder already ran, **When** it runs again, **Then** nothing is duplicated and nothing is deactivated.
3. **Given** a tissue state such as "Sloughing", **When** the seeder runs, **Then** it is not added as a wound type (it is already a wound-bed choice).

---

### Edge Cases

- A case registered before structured assessments existed (spec 012) has no structured findings: the report falls back to the legacy diagnosis description and legacy review dressings and medications, and leaves the tissue findings empty.
- A patient without a hospital admission date, birth date, ID number or gender: the cell is empty and the row is still listed; gender totals show an "unspecified" group only when such cases exist.
- A case discharged in a later month still belongs to its registration month; its discharge date shows the later date.
- A case whose wounds use a value that was later deactivated still shows that value's name.
- A department filter matching no cases shows the empty state.
- A month range whose start is after its end is rejected with an Arabic validation message.
- A patient's first registration predates the system: it is not detectable, so that patient's first recorded registration is not a readmission.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a monthly report section reachable from the main navigation by every authenticated user.
- **FR-002**: The case list MUST include every registration whose clinic registration date falls in the selected calendar month, ordered by registration date, numbered from 1.
- **FR-003**: Every report MUST accept an optional multi-select department filter; with no department selected, all departments are included.
- **FR-004**: The case list MUST show, per case: row number, patient name, month, ID number, gender, birth year, diagnoses, wound location and description, hospital admission date, past medical history, wound types with initial tissue findings, dressings, medications, procedures, department, number of wound photos, discharge date and outcome.
- **FR-005**: Wound-level columns (diagnoses, wound location and description, wound types and findings) MUST list the values of all wounds of the case, without duplicates.
- **FR-006**: Dressing, medication and procedure columns MUST list every distinct value recorded during the case; a swab taken in any assessment MUST appear as the procedure `swap culture`.
- **FR-007**: The outcome column MUST read `يستمر العلاج` for an active registration, `وفاة` when the last review outcome is death, `تحويل للمستشفى` when it is worsened-admission, and `خروج` for an improved discharge or a manual discharge.
- **FR-008**: The discharge date MUST be the date the registration was closed; it MUST be empty for an active registration.
- **FR-009**: The summary MUST cover a selectable month range, defaulting to the last 12 months, and show cases per month, cases per gender and cases per diagnosis, each with a total.
- **FR-010**: In the per-diagnosis counts, a case MUST count once under each distinct diagnosis it has; per-month and per-gender counts MUST count each case once.
- **FR-011**: The readmission list MUST include every registration in the selected period whose patient had any earlier registration, and MUST show the previous registration's date and outcome.
- **FR-012**: Users MUST be able to export the current report filters to an `.xlsx` file containing the case list, summary and readmission sheets, with right-to-left sheets and date-typed date cells.
- **FR-013**: System MUST record every export with the exporting user, time, period, departments and row count.
- **FR-014**: A seeder MUST load the workbook's value lists (diagnoses, wound types, dressings, medications, procedures, departments, past medical history), be safe to run repeatedly, never remove or deactivate existing values, and never add tissue states as wound types.
- **FR-015**: All report labels, messages and empty states MUST be in Arabic and render right-to-left.
- **FR-016**: Report figures MUST be computed from the clinical data at request time; no figure is entered by hand.

### Key Entities

- **Case**: One clinic registration of a patient; its month is the month of its clinic registration date. Carries the patient's demographics, department, wounds, treatments, photos, closure date and outcome.
- **Wound**: A diagnosis of the case with its wound type, location, and initial and follow-up assessments.
- **Readmission**: A case whose patient had an earlier case; linked to that earlier case.
- **Report Export Record**: Who exported which period and departments, when, and how many rows.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On a synthetic dataset reproducing the workbook (204 cases), the November–December 2025 reports show exactly the workbook's figures: 125/79 cases per month, 82 female/122 male, the six diagnosis counts, and 6 readmissions.
- **SC-002**: A user produces a month's case list and its export in under 1 minute, compared with manual compilation of the spreadsheet today.
- **SC-003**: A report covering 12 months and 2,000 cases opens in under 3 seconds.
- **SC-004**: 100% of exports are recorded with user, time, filters and row count.
- **SC-005**: An exported file opens in Excel with readable Arabic text, three sheets, and values identical to the screen for the same filters.

## Assumptions

- The doctor specialty column (`DR`) is omitted until a later feature adds specialties to registrations.
- The workbook's `Picture` column (a photo folder label) is represented by the number of wound photos recorded for the case.
- The workbook's `Discussion` column is represented by the wound location and description recorded for each wound.
- The hospital admission date comes from the patient record synced from the hospital and may be empty.
- The discharge date is taken from the time the registration was closed, since the separate discharge-date and discharge-type fields are not filled by the current discharge flows.
- Tests use synthetic patients whose aggregate distribution reproduces the workbook; no real patient data is stored in the repository.
- The exact assignment of each workbook value to a system list (for example `black tissue` → necrotic wound bed, `Pocketing` → pocketing wound margin, `blister` → wound type) is listed in the plan for review before seeding.
- Reports read existing clinical data only; they never change it, so the stored-procedure mandate for clinical mutations does not apply, and the aggregation is written so it can be ported to Oracle later (constitution principle VI).
