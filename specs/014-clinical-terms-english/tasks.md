# Tasks: Clinical Terms in English

**Input**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md), [data-model.md](data-model.md),
[contracts/clinical-glossary.md](contracts/clinical-glossary.md)

**Tests**: Included (constitution quality gate).

## Phase 1: Foundational

- [ ] T001 Create `lang/ar/clinical.php` with `fields`, `overall_outcomes`, `wound_outcomes`, `case_outcomes`, `genders` exactly as [contracts/clinical-glossary.md](contracts/clinical-glossary.md)
- [ ] T002 Create migration `<ts>_add_english_label_to_wc_assessment_choice_tb.php`: `WC_ACH_LABEL_EN` `VARCHAR(150)` nullable after `WC_ACH_LABEL_AR`, filled per `(WC_ACH_CATEGORY, WC_ACH_KEY)` from the glossary; `down()` drops the column
- [ ] T003 Add English names to `database/seeders/WcAssessmentChoiceSeeder.php` for fresh databases
- [ ] T004 Add `WC_ACH_LABEL_EN` to `$fillable` and `displayLabel(): string` (English name, else key in words) in `app/Models/WcAssessmentChoice.php`
- [ ] T005 Amend principle IV in `.specify/memory/constitution.md` (1.2.0 → 1.3.0) with the medical-terminology exception and a Sync Impact Report entry

## Phase 2: US1 — Monthly report (P1)

- [ ] T006 [US1] Workbook headers in `ReportFormat::CASE_COLUMNS` / `PREVIOUS_COLUMNS`, month `M -Y`, glossary-based outcome and gender labels in `app/Services/Reports/{ReportFormat,CaseOutcome,MonthlyCaseReportService}.php`; choice values via `displayLabel()`; PMH none → `No`
- [ ] T007 [US1] Sheet names `CASES`, `CASES-A`, `ReAdmission` and English summary headings in `app/Services/Reports/MonthlyReportWorkbook.php`; English terms LTR in `resources/views/reports/monthly/*`
- [ ] T008 [US1] Update `tests/Feature/{MonthlyCaseReportTest,MonthlyCaseSummaryTest,ReadmissionReportTest,MonthlyReportExportTest}.php` to the English terms and assert the 18 workbook headers

## Phase 3: US2 — Forms (P1)

- [ ] T009 [US2] Medical labels and `displayLabel()` choices in `resources/views/wound_assessments/partials/{fields,follow-up-fields}.blade.php`; outcome options from the glossary
- [ ] T010 [US2] Medical labels in `resources/views/requests/show.blade.php`, `resources/views/registrations/{create,partials/wound_diagnosis_modal}.blade.php`, `resources/views/reviews/{daily_create,scheduled}.blade.php`
- [ ] T011 [US2] Medical attribute names from the glossary in `app/Http/Requests/{AcceptRegistrationRequest,StoreWoundDiagnosisRequest,StoreDailyReviewRequest}.php` and `Concerns/ValidatesWoundAssessment.php`

## Phase 4: US3 — Reading pages (P2)

- [ ] T012 [US3] English terms in `resources/views/wound_assessments/partials/{summary,latest-comparison}.blade.php`, `reviews/partials/{review_timeline,wound_tabs}.blade.php`, `registrations/show.blade.php`, `patients/partials/registration_wounds.blade.php`, `reports/history.blade.php`, `dashboard.blade.php`
- [ ] T013 [US3] English outcome labels in `app/Services/ReportingService.php` and the CSV of `app/Http/Controllers/ReportController.php`

## Phase 5: US4 — Admin choices (P3)

- [ ] T014 [US4] English name field (required, max 150) in `app/Http/Requests/UpdateAssessmentChoiceRequest.php`, `app/Http/Controllers/Admin/AssessmentChoiceController.php`, `resources/views/admin/master_data/assessment-choices.blade.php`; update `tests/Feature/AssessmentChoiceAdminTest.php`

## Phase 6: Tests & polish

- [ ] T015 Create `tests/Feature/ClinicalTermsDisplayTest.php`: forms show English medical labels/choices and Arabic administrative labels; validation message names the English field; timeline/summary show English outcomes and values; free text shown as written; every seeded choice has an English name after migration
- [ ] T016 Update `tests/Feature/WoundAssessmentModelTest.php` and any other test asserting Arabic medical terms
- [ ] T017 Full suite on SQLite; report, choice and display tests on MySQL; `vendor/bin/pint --dirty --format agent`; `npm run build`
- [ ] T018 Migrate the WAMP database; manual check per [quickstart.md](quickstart.md); mark tasks
