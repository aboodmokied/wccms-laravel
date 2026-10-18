# Research: Monthly Case Reports

All decisions below build on the clarifications recorded in [spec.md](spec.md). Facts about the current
system were verified against the code and the local MySQL schema on 2026-10-19.

## R1. Where the aggregation runs

- **Decision**: A read-only PHP service (`MonthlyCaseReportService`) loads the registrations of the period with
  eager-loaded relations through the query builder/Eloquent, then builds rows and counts in PHP.
- **Rationale**: Cells such as "all diagnoses of the case" need string aggregation, which differs per engine
  (`GROUP_CONCAT` on MySQL and SQLite, `LISTAGG` on Oracle). Doing it in PHP keeps one implementation that runs on
  the SQLite test database, local MySQL and later Oracle (constitution VI). Volume is small: the clinic registers
  about 100–125 cases per month, so 12 months is under 2,000 registrations.
- **Alternatives considered**: A MySQL view with `GROUP_CONCAT` (engine-specific, untestable on SQLite, rewritten for
  Oracle); a stored procedure (constitution II mandates procedures for clinical mutations only — reports change no
  clinical data).

## R2. Month of a case

- **Decision**: `WC_REG_DATE` in `[first day of month 00:00, first day of next month 00:00)`, in the application
  time zone.
- **Rationale**: Clarification Q2. `WC_REG_DATE` is always set when a request is accepted (stored procedure
  `SP_WC_ACCEPT_REGISTRATION_WITH_ASSESSMENTS`).

## R3. Column sources

| Report column | Source | Legacy fallback (cases before spec 012) |
|---|---|---|
| Patient name | `mr_patient_tb.MR_PAT_FULL_NAME` | — |
| ID number | `MR_PAT_NATIONAL_ID`, else `MR_PAT_ID_NO` | — |
| Gender | `MR_PAT_GENDER` (`MALE`/`M` → ذكر, `FEMALE`/`F` → أنثى), as in `patient_demographics_banner.blade.php` | — |
| Birth year | year of `MR_PAT_DOB` | — |
| Hospital admission date (DoA) | `MR_PAT_HOSPITAL_ADMISSION_DATE` | — |
| Department | `wc_department_tb` via `WC_REG_DEPARTMENT_CD` | — |
| Past medical history | `wc_registration_pmh_tb` → `wc_pmh_tb`; `WC_REG_NO_COMORBIDITIES` = 1 → `لا يوجد` | `WC_REG_PMH_NOTES` |
| Diagnoses | `wc_reg_diagnosis_tb` → `wc_diagnosis_tb`, distinct | — |
| Wound location and description | initial assessment location choice + laterality + `WC_WA_LOCATION_NOTES` | `WC_RDIAG_DESCRIPTION` |
| Wound types and findings | `wc_wound_type_tb` + initial-assessment findings in `WOUND_BED` and `WOUND_MARGIN` | wound type only |
| Dressings / medications | every assessment (`WC_WA_DRESSING_CD`, `WC_WA_MEDICATION_CD`) and review detail (`WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`), distinct | review details only |
| Procedures | `WC_REG_PROCEDURE_CD` + every `WC_WA_PROCEDURE_CD`, distinct; `swap culture` when any `WC_WA_SWAB_TAKEN` = 1 | registration procedure only |
| Photos | count of `wc_wound_photo_tb` rows of the case's diagnoses | same |
| Discharge date | date part of `WC_REG_DISCHARGED_AT`, else `WC_REG_DISCHARGE_DATE` | same |
| Outcome | see R4 | same |

- **ID number**: the workbook's IDs are 9-digit national IDs; the seeded `MR_PAT_ID_NO` values (`PAT-1001`) are
  clinic numbers. The national ID is shown first, with the clinic number as fallback.
- **Discharge date**: verified that no code writes `WC_REG_DISCHARGE_DATE` or `WC_REG_DISCHARGE_TYPE`
  (`RegistrationController.php:235`, `SqliteClinicalWorkflowGateway.php:231` and the review procedure write
  `WC_REG_DISCHARGED_AT` only). Recorded as an out-of-scope problem.

## R4. Outcome

- **Decision**:
  - `WC_REG_STATUS = 'ACTIVE'` → `يستمر العلاج`
  - otherwise, the registration's latest daily review by `WC_DREV_VISIT_DATE`:
    - `DEATH` → `وفاة`
    - `WORSENED_ADMISSION` → `تحويل للمستشفى`
    - anything else, or no review → `خروج`
- **Rationale**: Clarification Q11. The latest review is the one that closed the registration when the review
  flow discharged it. A manual discharge (`RegistrationController@discharge`) after any non-closing review lands in
  `خروج`.

## R5. Readmissions

- **Decision**: For the registrations of the period, load all registrations of the same patients
  (`WC_REG_PATIENT_CD`) and pick, for each, the latest earlier one (`WC_REG_DATE` strictly earlier; ties broken by
  code).
- **Rationale**: Clarification Q9, equivalent to the workbook formula
  `COUNTIF(INDEX(cases[ID],1):x, x) > 1`. Matching uses the patient record, not the ID text, so a case without an ID
  number is still matched (the workbook misses those).

## R6. Excel export library

- **Decision**: `openspout/openspout` (v4), added to `composer.json` `require`.
- **Rationale**: Clarification Q7. It writes `.xlsx` as a stream (low memory), supports several sheets,
  right-to-left sheet views and date-typed cells, and has no extension requirements beyond those Laravel already
  needs (`zip`, `xmlwriter`).
- **Alternatives considered**: `phpoffice/phpspreadsheet` (heavier, holds the workbook in memory, larger
  dependency); `maatwebsite/excel` (wraps PhpSpreadsheet, adds a package layer).

## R7. Export record

- **Decision**: A new table `wc_report_export_tb` written with Eloquent after the file is built.
- **Rationale**: FR-013. No audit table exists today. Recording an export is not a clinical workflow, so the
  stored-procedure mandate (constitution II) does not apply; the table follows the naming protocol (III) and is
  created with the fluent schema builder (VI).

## R8. Charts

- **Decision**: Server-rendered markup: horizontal CSS bars for the per-month and per-diagnosis counts and an inline
  SVG pie for gender, with the numbers always printed next to each mark.
- **Rationale**: Matches spec 009's choice of no chart library; the charts stay readable without JavaScript and in
  print.

## R9. Synthetic test data

- **Decision**: A test fixture builds 204 synthetic registrations from aggregate counts only (month × gender ×
  diagnosis, 21 cells, taken from the workbook) plus 6 readmission pairs placed as in the workbook (1 within
  November, 5 from November to December).
- **Rationale**: SC-001 needs the workbook's exact figures; aggregate counts contain no personal data. No name, ID
  or date from the workbook is copied.

## R10. Value lists

- **Decision**: One idempotent seeder, `WcClinicWorkbookListsSeeder`, using `firstOrCreate` by name per list (the
  convention of the existing master-data seeders). The value-to-list assignment is in
  [contracts/value-list-mapping.md](contracts/value-list-mapping.md) for review before implementation.
- **Rationale**: Clarification Q6 and FR-014.

## Out-of-scope problems found

1. Discharge flows never fill `WC_REG_DISCHARGE_DATE` / `WC_REG_DISCHARGE_TYPE`, while
   `ReportingService::getKpis()` reads `WC_REG_DISCHARGE_DATE` for the average treatment duration.
2. `ReportingService::getKpis()` counts active patients with `WC_REG_STATUS = 'REGISTERED'`; registrations are
   stored as `ACTIVE`, so the dashboard's active-patient KPI is always 0.
3. Doctor specialty (`DR`) — deferred feature (clarification Q4).
