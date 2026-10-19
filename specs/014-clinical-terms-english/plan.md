# Implementation Plan: Clinical Terms in English

**Branch**: `014-clinical-terms-english` | **Date**: 2026-10-19 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/014-clinical-terms-english/spec.md`

## Summary

Display every medical term in English on every page while administrative text stays Arabic. One glossary
(`lang/ar/clinical.php`) supplies field labels, outcomes and gender; assessment choices gain an English name column
filled by the migration; forms, reading pages, validation attribute names, the patient-history CSV and the monthly
report (workbook headers, `Male`/`Female`, `Nov -2025`, English outcomes, workbook sheet names) read from them. The
constitution's principle IV records the exception. Branched from `013-monthly-case-reports`.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13 · **Primary Dependencies**: existing stack, no new package

**Storage**: MySQL 8.4 (Oracle later); one new column `wc_assessment_choice_tb.WC_ACH_LABEL_EN`

**Testing**: PHPUnit feature tests (SQLite), report and choice tests also on MySQL

**Project Type**: Laravel monolith (Blade) · **Constraints**: no change to stored clinical text; Arabic RTL layout kept

**Scale/Scope**: ~20 views, ~8 PHP classes, 1 migration, 1 language file, 6 test files to update

## Constitution Check

| Principle | Status | Notes |
|---|---|---|
| I. Stack | Pass | |
| II. DB-first clinical logic | Pass | No clinical workflow changes; the stored procedures are untouched. |
| III. Naming | Pass | `WC_ACH_LABEL_EN` follows the column prefix. |
| IV. Arabic & RTL | **Amended** | Exception for medical terminology, decided by the user; amendment 1.3.0 in this feature ([research.md](research.md) R5). Everything else stays Arabic and RTL. |
| V. Actor vs data entry | Pass | Unchanged. |
| VI. MySQL-first, Oracle-later | Pass | Fluent schema builder; the data fill uses the query builder. |

## Project Structure

```text
specs/014-clinical-terms-english/
├── spec.md, plan.md, research.md, data-model.md, quickstart.md
└── contracts/clinical-glossary.md

lang/ar/clinical.php                                   # NEW glossary
database/migrations/<ts>_add_english_label_to_wc_assessment_choice_tb.php   # NEW column + fill
database/seeders/WcAssessmentChoiceSeeder.php          # English names for fresh databases
app/Models/WcAssessmentChoice.php                      # fillable + displayLabel()
app/Http/Requests/{UpdateAssessmentChoiceRequest,AcceptRegistrationRequest,StoreWoundDiagnosisRequest,StoreDailyReviewRequest}.php
app/Http/Requests/Concerns/ValidatesWoundAssessment.php
app/Http/Controllers/{Admin/AssessmentChoiceController,ReportController}.php
app/Services/{ReportingService.php,Reports/CaseOutcome.php,Reports/ReportFormat.php,Reports/MonthlyCaseReportService.php,Reports/MonthlyReportWorkbook.php}
resources/views/…                                      # the views listed in research.md "Inventory"
.specify/memory/constitution.md                        # principle IV amendment, 1.3.0
tests/Feature/ClinicalTermsDisplayTest.php             # NEW: forms and reading pages
tests/Feature/{AssessmentChoiceAdminTest,WoundAssessmentModelTest,MonthlyCaseReportTest,MonthlyCaseSummaryTest,MonthlyReportExportTest}.php
```

## Delivery order

1. Glossary file, migration + seeder + model (`displayLabel()`), constitution amendment.
2. Monthly report (US1) — smallest, self-contained, matches the workbook.
3. Forms (US2): shared assessment partials first (they cover four screens), then registration/review specifics and
   validation attribute names.
4. Reading pages (US3) and the patient-history CSV.
5. Admin choice screen (US4).
6. Update tests asserting Arabic medical terms; full suite on SQLite; report/choice tests on MySQL; Pint; build;
   manual RTL check.

## Complexity Tracking

| Item | Why | Simpler alternative rejected |
|---|---|---|
| Constitution IV exception | The user's rule: medical terms are never translated | Leaving IV unchanged would make every such page a violation |
