# Tasks: Monthly Case Reports

**Input**: Design documents from `/specs/013-monthly-case-reports/`

**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md), [data-model.md](data-model.md),
[contracts/](contracts/), [quickstart.md](quickstart.md)

**Tests**: Included — every user story has an independent test in the spec, and the constitution's quality gate
requires PHPUnit feature tests.

**Format**: `[ID] [P?] [Story] Description` — `[P]` = can run in parallel (different files, no dependency on an
unfinished task).

## Phase 1: Setup

- [x] T001 Add `openspout/openspout` (resolved ^5.3) to `composer.json` `require` with `composer require openspout/openspout --no-interaction` and commit the updated `composer.lock`
- [x] T002 [P] Create `resources/css/pages/reports.css` using the existing `m8-` design tokens registered as a Vite input in `vite.config.js` and loaded per page with `@vite` (how the other page stylesheets are loaded)

---

## Phase 2: Foundational (blocks all user stories)

- [x] T003 Create `tests/WorkbookSyntheticCases.php` (project convention: helpers at the root of `tests/`, like `tests/StructuredWoundTestCase.php`): builds synthetic patients, departments, diagnoses and registrations from the 21 aggregate (month × gender × diagnosis) counts in [research.md](research.md) R9 (Nov 2025: Female Burn 1, DF 23, Inf Wound 15, SSI 2, bedsore PEU 11; Male Burn 6, DF 23, Inf Wound 16, Row Area 5, SSI 2, bedsore PEU 21 — Dec 2025: Female Burn 2, DF 9, Inf Wound 9, bedsore PEU 10; Male Burn 2, DF 14, Inf Wound 15, Row Area 2, SSI 7, bedsore PEU 9) plus 6 readmissions (1 within November, 5 from November to December); generated names and IDs only, nothing copied from the workbook
- [x] T004 Create the read model `app/Services/Reports/CaseRow.php` with the fields of [data-model.md](data-model.md) "CaseRow", including the outcome values `ONGOING` / `DISCHARGED` / `DEATH` / `HOSPITAL_TRANSFER` and their Arabic labels `يستمر العلاج` / `خروج` / `وفاة` / `تحويل للمستشفى`
- [x] T005 Create `app/Http/Requests/MonthlyReportRequest.php`: `month`, `from`, `to` as `date_format:Y-m`; `to` `after_or_equal:from`; `departments` array of `exists:wc_department_tb,WC_DEPT_CODE`; Arabic messages from [contracts/report-pages.md](contracts/report-pages.md); defaults (current month; last 12 months for the summary)
- [x] T006 Create `app/Services/Reports/MonthlyCaseReportService.php` with `cases(Carbon $from, Carbon $to, array $departmentCodes): Collection<CaseRow>`: registrations with `WC_REG_DATE` in `[first day of from, first day of month after to)`, ordered by `WC_REG_DATE` then `WC_REG_CODE`, eager-loading `patient`, `department`, `procedure`, `pmhConditions`, `diagnoses.diagnosis`, `diagnoses.woundType`, `diagnoses.assessments.{dressing,medication,procedure,location,laterality,findings.choice}`, `diagnoses.dailyReviewDetails.{dressing,medication}`, `diagnoses.woundPhotos`, `dailyReviews`; column rules and legacy fallbacks exactly as [research.md](research.md) R3, outcome as R4; distinct values in first-seen order; no engine-specific SQL

**Checkpoint**: the service returns rows for a period; stories can start.

---

## Phase 3: User Story 1 — Monthly case list (P1) 🎯 MVP

**Goal**: one-month case list, one row per case, department filter.

**Independent test**: seed cases across two months and departments, open one month, verify rows and every column.

- [x] T007 [P] [US1] Create `tests/Feature/MonthlyCaseReportTest.php` covering spec US1 scenarios 1–6: 125 rows for Nov 2025 from the synthetic set, numbered and ordered; a two-wound case in one row with both diagnoses; a dressing change listed once each; department filter (Orthopedic Male/Female, Special Male/Female); active case with empty discharge date and `يستمر العلاج`; empty month message; plus the outcome mapping of R4 (`DEATH`, `WORSENED_ADMISSION`, manual discharge), the swab → `swap culture` procedure, a legacy case without structured assessments, national ID before clinic number, and an invalid `month` showing `صيغة الشهر غير صحيحة.`
- [x] T008 [US1] Create `app/Http/Controllers/MonthlyReportController.php` with `cases(MonthlyReportRequest $request)` returning `reports.monthly.cases` with the rows, month, departments list and selected departments
- [x] T009 [US1] Add `GET /reports/monthly` named `reports.monthly.cases` inside the `hospital.jwt` group in `routes/web.php`
- [x] T010 [P] [US1] Create `resources/views/reports/monthly/partials/tabs.blade.php` and `resources/views/reports/monthly/partials/filters.blade.php` (month or from/to pickers, department multi-select, export button) per [contracts/report-pages.md](contracts/report-pages.md)
- [x] T011 [P] [US1] Create `resources/views/reports/monthly/partials/case-table.blade.php` with the FR-004 columns in workbook order, escaped values, horizontal scroll inside its card on narrow screens
- [x] T012 [US1] Create `resources/views/reports/monthly/cases.blade.php` (layout `layouts.app`, tabs, filters, case table, empty state `لا توجد حالات في هذا الشهر.`)
- [x] T013 [US1] Add the `التقارير الشهرية` entry (active on `reports.monthly.*`) to the "السجلات والتقارير" section of `resources/views/partials/sidebar.blade.php`, keeping the existing `التقارير` entry
- [x] T014 [US1] Run `php artisan test --compact tests/Feature/MonthlyCaseReportTest.php` and fix until it passes

**Checkpoint**: US1 usable and demonstrable on its own.

---

## Phase 4: User Story 2 — Case summary over a month range (P2)

**Goal**: per-month, per-gender and per-diagnosis counts with charts.

**Independent test**: synthetic set, Nov–Dec 2025, every count equals SC-001.

- [x] T015 [P] [US2] Create `tests/Feature/MonthlyCaseSummaryTest.php` covering spec US2 scenarios 1–4: 125/79, 82 female/122 male, 51/11/69/55/7/11 diagnoses, total 204; a two-diagnosis case counted under both diagnoses but once per month and gender; default range = last 12 months including the current month; zero months present; `to` before `from` rejected with the Arabic message
- [x] T016 [US2] Add `summary(Carbon $from, Carbon $to, array $departmentCodes): array` to `app/Services/Reports/MonthlyCaseReportService.php` returning `perMonth` (every month of the range, zeros included), `perGender` (ذكر / أنثى, غير محدد only when present), `perDiagnosis` (count descending then name) and `totalCases`, per [data-model.md](data-model.md) "CaseSummary"
- [x] T017 [US2] Add `summary()` to `app/Http/Controllers/MonthlyReportController.php` and `GET /reports/monthly/summary` named `reports.monthly.summary` in `routes/web.php`
- [x] T018 [US2] Create `resources/views/reports/monthly/summary.blade.php`: CSS bars for months and diagnoses, inline SVG pie for gender, numbers printed beside every mark, totals ([research.md](research.md) R8)
- [x] T019 [US2] Run `php artisan test --compact tests/Feature/MonthlyCaseSummaryTest.php` and fix until it passes

---

## Phase 5: User Story 3 — Readmissions (P2)

**Goal**: registrations whose patient had an earlier registration, with the previous date and outcome.

**Independent test**: patient registered in Nov and Dec; December list shows the Dec registration with the Nov date.

- [x] T020 [P] [US3] Create `tests/Feature/ReadmissionReportTest.php` covering spec US3 scenarios 1–4: Dec shows the 1 Dec registration with previous date 22 Nov and its outcome; Nov does not show that patient; a previous registration a year earlier still counts; synthetic set Nov–Dec 2025 gives 6 rows; a patient without an ID number is still matched
- [x] T021 [US3] Add `readmissions(Carbon $from, Carbon $to, array $departmentCodes): Collection` to `app/Services/Reports/MonthlyCaseReportService.php`: for the period's registrations, the latest earlier registration of the same `WC_REG_PATIENT_CD` (`WC_REG_DATE` strictly earlier, ties by code), adding `previousRegisteredAt` and `previousOutcome` ([research.md](research.md) R5)
- [x] T022 [US3] Add `readmissions()` to `app/Http/Controllers/MonthlyReportController.php` and `GET /reports/monthly/readmissions` named `reports.monthly.readmissions` in `routes/web.php`
- [x] T023 [US3] Create `resources/views/reports/monthly/readmissions.blade.php` reusing the case table with the columns "التسجيل السابق" and "نتيجة التسجيل السابق", empty state `لا توجد حالات إعادة دخول في هذه الفترة.`
- [x] T024 [US3] Run `php artisan test --compact tests/Feature/ReadmissionReportTest.php` and fix until it passes

---

## Phase 6: User Story 4 — Excel export (P3)

**Goal**: `.xlsx` with three RTL sheets; every export recorded.

**Independent test**: export Nov 2025, read the file back: three sheets, values equal the screen, one export record.

- [x] T025 [P] [US4] Create `tests/Feature/MonthlyReportExportTest.php` covering spec US4 scenarios 1–3: response is an `.xlsx` download named `monthly-cases-YYYY-MM.xlsx` (or `-to-YYYY-MM` for a range); reading it back with OpenSpout gives sheets `الحالات`, `الملخص`, `إعادة الدخول` with the same rows and counts as the service for the same filters; sheets are right-to-left; Arabic names intact; date cells are dates; one `wc_report_export_tb` row with the user, period, department codes and case count
- [x] T026 [US4] Create migration `database/migrations/<current UTC timestamp>_create_wc_report_export_tb.php` with the fluent schema builder: `WC_RPEX_CODE` `CHAR(36)` primary key; `WC_RPEX_USER_CD` `CHAR(36)` required, FK → `per_users_tb.PER_USER_CODE`; `WC_RPEX_EXPORTED_AT` `DATETIME` required, indexed; `WC_RPEX_PERIOD_FROM` `DATE` required; `WC_RPEX_PERIOD_TO` `DATE` required; `WC_RPEX_DEPARTMENT_CODES` `TEXT` nullable (JSON array, null = all); `WC_RPEX_CASE_COUNT` `INT` required; Arabic column comments like the core tables
- [x] T027 [P] [US4] Create `app/Models/WcReportExport.php` per `.ai/rules/db-first.md`: `$table = 'wc_report_export_tb'`, `$primaryKey = 'WC_RPEX_CODE'`, `$keyType = 'string'`, `$incrementing = false`, `$timestamps = false`, fillable and casts (`WC_RPEX_DEPARTMENT_CODES` as array), `user()` BelongsTo
- [x] T028 [US4] Create `app/Services/Reports/MonthlyReportWorkbook.php`: writes the three sheets with OpenSpout (bold header row, right-to-left sheet view, date-typed date cells, numeric counts) to the response stream
- [x] T029 [US4] Add `export()` to `app/Http/Controllers/MonthlyReportController.php` (`from`/`to` required, departments optional), stream the file, then insert the `WcReportExport` row; add `GET /reports/monthly/export` named `reports.monthly.export` in `routes/web.php`; wire the export buttons in `resources/views/reports/monthly/partials/filters.blade.php` (case list exports `from = to = month`)
- [x] T030 [US4] Run `php artisan test --compact tests/Feature/MonthlyReportExportTest.php` and fix until it passes

---

## Phase 7: User Story 5 — Clinic value lists (P3)

**Goal**: the workbook's lists exist in the system, safely and once.

**Independent test**: run the seeder twice on a populated database; every listed value exists once, nothing removed or deactivated.

- [x] T031 [P] [US5] Create `tests/Feature/ClinicWorkbookListsSeederTest.php` covering spec US5 scenarios 1–3: every value of [contracts/value-list-mapping.md](contracts/value-list-mapping.md) exists and is active; a second run adds nothing; pre-existing demo values keep their active state; skipped values (`Sloughing`, `Necrotic`, `black tissue`, `Granulation`, `Pocketing`, `swap culture`, `Depredment`, `NO`, `No`) are absent from the target lists
- [x] T032 [US5] Create `database/seeders/WcClinicWorkbookListsSeeder.php` with `firstOrCreate` by name per list (diagnoses, wound types, dressings, medications, procedures, departments, PMH) exactly as [contracts/value-list-mapping.md](contracts/value-list-mapping.md), never updating `IS_ACTIVE` of existing rows
- [x] T033 [US5] Register `WcClinicWorkbookListsSeeder` in `database/seeders/DatabaseSeeder.php` after the existing master-data seeders
- [x] T034 [US5] Run `php artisan test --compact tests/Feature/ClinicWorkbookListsSeederTest.php` and fix until it passes

---

## Phase 8: Polish & cross-cutting

- [x] T035 Run `php artisan test --compact` (full suite, SQLite) and fix any regression
- [x] T036 Run the report test files on MySQL against a freshly migrated `wccms_test` as in [quickstart.md](quickstart.md), one process per file, and fix any engine difference
- [x] T037 Run `vendor/bin/pint --dirty --format agent` and `npm run build`; fix failures
- [ ] T038 (partly done: pages and export return 200 on the WAMP data and the export is recorded; the visual desktop and phone-width RTL check is left to the user) Perform the manual check in [quickstart.md](quickstart.md) steps 1–8 on the WAMP database, including phone width
- [x] T039 Reconcile every FR and SC of [spec.md](spec.md) against the implementation and mark completed tasks in this file

---

## Dependencies

- Phase 1 → Phase 2 → stories.
- US1 (T007–T014) first: it creates the controller, routes file entries, partials and nav that US2–US4 reuse.
- US2 and US3 depend only on Phase 2 and the US1 controller/partials; they can proceed in parallel after T012.
- US4 depends on US2 and US3 (the workbook writes all three).
- US5 is independent of US1–US4 (master data only) and can run any time after Phase 1.

## Parallel opportunities

- T002 with T001.
- Test files T007, T015, T020, T025, T031 can be written in parallel with each other.
- T010 and T011 in parallel; T027 in parallel with T026.
- US5 (T031–T034) in parallel with any story phase.

## Implementation strategy

1. **MVP**: Phases 1–3 (US1) — the monthly list the clinic produces every month.
2. Add US2 and US3, then US4 export, then US5 lists.
3. Phase 8 before the branch is considered done.
