# Data Model: Monthly Case Reports

The reports read existing tables only. One table is added, for export records.

## New table: `wc_report_export_tb`

| Column | Type | Rules |
|---|---|---|
| `WC_RPEX_CODE` | `CHAR(36)` | Primary key, UUID |
| `WC_RPEX_USER_CD` | `CHAR(36)` | Required; FK → `per_users_tb.PER_USER_CODE` (the data-entry user who exported) |
| `WC_RPEX_EXPORTED_AT` | `DATETIME` | Required |
| `WC_RPEX_PERIOD_FROM` | `DATE` | Required; first day of the first month |
| `WC_RPEX_PERIOD_TO` | `DATE` | Required; first day of the last month; `>= WC_RPEX_PERIOD_FROM` |
| `WC_RPEX_DEPARTMENT_CODES` | `TEXT` | Nullable; JSON array of `WC_DEPT_CODE`; null = all departments |
| `WC_RPEX_CASE_COUNT` | `INT` | Required; rows in the case-list sheet |

- Index on `WC_RPEX_EXPORTED_AT`.
- Insert-only: the application never updates or deletes rows.
- Model `App\Models\WcReportExport`: explicit `$table`, `$primaryKey`, `$keyType = 'string'`,
  `$incrementing = false`, `$timestamps = false` (DB-first rules in `.ai/rules/db-first.md`).

## Read model (not stored)

### CaseRow

One per registration in the period.

| Field | Source (see [research.md](research.md) R3) |
|---|---|
| `number` | position in the period's ordering (1-based) |
| `registrationCode`, `registeredAt`, `month` | `wc_registration_tb` |
| `patientCode`, `patientName`, `idNumber`, `gender`, `birthYear`, `hospitalAdmissionDate` | `mr_patient_tb` |
| `department` | `wc_department_tb` |
| `pastMedicalHistory` | list of names, or `لا يوجد` |
| `diagnoses`, `woundLocations`, `woundTypes`, `dressings`, `medications`, `procedures` | distinct lists, in first-seen order |
| `photoCount` | integer |
| `dischargeDate` | date or null |
| `outcome` | `ONGOING` / `DISCHARGED` / `DEATH` / `HOSPITAL_TRANSFER`, labelled `يستمر العلاج` / `خروج` / `وفاة` / `تحويل للمستشفى` |

### CaseSummary

| Field | Rule |
|---|---|
| `perMonth` | every month of the range, including zero months, with its case count |
| `perGender` | ذكر / أنثى, plus غير محدد only when present; each case counted once |
| `perDiagnosis` | each case counted once under each distinct diagnosis it has, sorted by count descending then name |
| `totalCases` | number of cases in the range |

### ReadmissionRow

A `CaseRow` plus `previousRegisteredAt` and `previousOutcome` from the patient's latest earlier registration.

## Filters

| Filter | Rule |
|---|---|
| `month` | `YYYY-MM`; case list only; default the current month |
| `from`, `to` | `YYYY-MM`; summary, readmissions and export; default last 12 months (summary) or the current month (readmissions); `from <= to` |
| `departments[]` | optional list of existing `WC_DEPT_CODE`; empty = all |
