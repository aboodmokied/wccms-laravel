# Implementation Plan: Monthly Case Reports

**Branch**: `013-monthly-case-reports` | **Date**: 2026-10-19 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/013-monthly-case-reports/spec.md`

## Summary

Add a monthly reports section that reproduces the clinic workbook: a one-month case list (one row per
registration, wound and treatment values merged per case), a month-range summary (per month, gender, diagnosis), a
readmission list, a department multi-select filter on all three, and an `.xlsx` export with three sheets whose every
use is recorded. A seeder loads the workbook's value lists. Aggregation runs in a read-only PHP service over eager-
loaded registrations so the same code works on SQLite (tests), MySQL and later Oracle ([research.md](research.md)
R1).

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: existing stack (Bootstrap 5.3 RTL, Bootstrap Icons, Tailwind 4 via Vite, `m8-` design
tokens); new: `openspout/openspout` ^4 for `.xlsx` ([research.md](research.md) R6)

**Storage**: MySQL 8.4 (local, WAMP), Oracle later; one new table `wc_report_export_tb`
([data-model.md](data-model.md))

**Testing**: PHPUnit feature tests with `RefreshDatabase` on in-memory SQLite; the report tests also run on MySQL
([quickstart.md](quickstart.md))

**Target Platform**: Web application served by Apache/`php artisan serve`

**Project Type**: Laravel monolith (Blade views)

**Performance Goals**: 12-month summary over 2,000 registrations in under 3 s (SC-003)

**Constraints**: read-only over clinical data; no personal data from the workbook in the repository; Arabic RTL

**Scale/Scope**: about 100–125 registrations per month; 3 pages, 1 export, 1 table, 1 seeder

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|---|---|---|
| I. Stack | Pass | Laravel; MySQL now, Oracle later. |
| II. DB-first, in-database clinical logic | Pass | Reports change no clinical data. The only write is the export record, which is not a clinical workflow (same exemption as master-data CRUD). The seeder adds master data only. |
| III. Naming | Pass | `wc_report_export_tb`, columns `WC_RPEX_*`, PK `WC_RPEX_CODE`, FK `WC_RPEX_USER_CD`. |
| IV. Arabic & RTL | Pass | All labels and messages Arabic; RTL pages and RTL export sheets. |
| V. Actor vs data entry | Pass | An export is a system-user action, not a clinical one; it records the user (`per_users_tb`). |
| VI. MySQL-first, Oracle-later | Pass | Fluent schema builder; no engine-specific SQL (string aggregation in PHP, R1). |

Re-checked after Phase 1 design: unchanged.

## Project Structure

### Documentation (this feature)

```text
specs/013-monthly-case-reports/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── report-pages.md
│   └── value-list-mapping.md
└── tasks.md             # /speckit-tasks
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/MonthlyReportController.php     # cases, summary, readmissions, export
│   └── Requests/MonthlyReportRequest.php           # month / from / to / departments, Arabic messages
├── Models/WcReportExport.php
└── Services/Reports/
    ├── MonthlyCaseReportService.php                # rows, summary, readmissions (read-only)
    ├── CaseRow.php                                 # read model
    └── MonthlyReportWorkbook.php                   # openspout writer: 3 RTL sheets

database/
├── migrations/<timestamp>_create_wc_report_export_tb.php
└── seeders/WcClinicWorkbookListsSeeder.php         # + registered in DatabaseSeeder

resources/
├── views/reports/monthly/
│   ├── cases.blade.php
│   ├── summary.blade.php
│   ├── readmissions.blade.php
│   └── partials/{filters,tabs,case-table}.blade.php
├── views/partials/sidebar.blade.php                # new nav entry
└── css/pages/reports.css

routes/web.php                                      # 4 routes in the hospital.jwt group

tests/
├── Support/WorkbookSyntheticCases.php              # 204 synthetic cases from aggregate counts (R9)
└── Feature/
    ├── MonthlyCaseReportTest.php                   # US1
    ├── MonthlyCaseSummaryTest.php                  # US2, SC-001
    ├── ReadmissionReportTest.php                   # US3
    ├── MonthlyReportExportTest.php                 # US4 (reads the file back with openspout)
    └── ClinicWorkbookListsSeederTest.php           # US5
```

**Structure Decision**: Laravel monolith, following the existing `ReportController` / `ReportingService` split; the
new code gets its own controller and a `Services/Reports` folder so the existing reporting service is untouched.

## Delivery order

1. US1 case list (service, request, controller, page, nav) — MVP.
2. US2 summary and US3 readmissions (same service).
3. US4 export (dependency, migration, model, workbook writer).
4. US5 seeder (after the mapping in [contracts/value-list-mapping.md](contracts/value-list-mapping.md) is approved).
5. Full suite on SQLite, report tests on MySQL, Pint, build, manual RTL check.

## Complexity Tracking

No constitution violations.
