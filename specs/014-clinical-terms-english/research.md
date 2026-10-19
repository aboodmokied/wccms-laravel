# Research: Clinical Terms in English

## R1. One glossary for every page

- **Decision**: A Laravel translation file `lang/ar/clinical.php` holds the English display names of medical field
  labels, review outcomes, per-wound outcomes, case outcomes and gender. Views, form-request attribute names, the
  report and the CSV export read it with `__('clinical.…')`.
- **Rationale**: The project already keeps UI strings in `lang/ar/*.php` (`ui.php`, `status.php`). Today the same
  outcome is labelled differently in several views (for example `IMPROVED_DISCHARGE` is both `تحسن وتخريج` and
  `مكتمل الشفاء` in `review_timeline.blade.php`); one file removes those copies.
- **Alternatives considered**: A PHP class of constants (works, but bypasses the localisation mechanism the project
  uses); English strings inline in each view (repeats the current drift).

## R2. English names of assessment choices

- **Decision**: New column `WC_ACH_LABEL_EN` (`VARCHAR(150)`) on `wc_assessment_choice_tb`, added nullable, filled by
  the same migration from [contracts/clinical-glossary.md](contracts/clinical-glossary.md) keyed by
  `(WC_ACH_CATEGORY, WC_ACH_KEY)`, then made required for new and edited choices by validation.
  `WcAssessmentChoiceSeeder` writes it for fresh databases. The model exposes `displayLabel()`: the English name, else
  the key turned into words.
- **Rationale**: Clarification Q2. Filling inside the migration keeps every existing database consistent without a
  manual seed; keys are stable (spec 012), so the fill is deterministic.
- **Alternatives considered**: Overwriting `WC_ACH_LABEL_AR` (loses Arabic, misnamed column); deriving names from keys
  only (not editable, poor wording for some keys).

## R3. Validation messages

- **Decision**: Form requests keep Arabic messages and set medical attribute names from the glossary, so Laravel's
  Arabic templates produce `حقل Exudate Amount مطلوب.`.
- **Rationale**: FR-005; Arabic messages stay Arabic, only the term is English.

## R4. Direction

- **Decision**: Medical terms are wrapped in `<bdi>` (or rendered in cells with `dir="ltr"` where a cell holds only
  English) so they keep left-to-right order inside RTL pages.
- **Rationale**: FR-009; avoids punctuation and parentheses flipping around English words.

## R5. Constitution

- **Decision**: Amend principle IV (version 1.2.0 → 1.3.0): "Medical terminology (clinical field labels, assessment
  values, clinical outcomes) is displayed in English on every page; all other user-facing text remains Arabic."
- **Rationale**: FR-010; the constitution's governance section requires an amendment for a change to a principle.

## R6. Monthly report

- **Decision**: `ReportFormat::CASE_COLUMNS` takes the workbook headers; `ReportFormat::month()` returns `M -Y`
  (`Nov -2025`); `CaseOutcome::label()` and gender read the glossary; sheet names `CASES`, `CASES-A`, `ReAdmission`.
- **Rationale**: Clarifications; spec 013 user story 4 wording.

## Inventory (files that display medical terms in Arabic today)

Views: `wound_assessments/partials/{fields,follow-up-fields,latest-comparison,summary}`, `requests/show`,
`registrations/{create,show}`, `registrations/partials/wound_diagnosis_modal`, `reviews/{daily_create,scheduled}`,
`reviews/partials/{review_timeline,wound_tabs}`, `patients/partials/registration_wounds`, `reports/history`,
`reports/monthly/*`, `dashboard`, `admin/master_data/assessment-choices`.
Code: `Http/Requests/{AcceptRegistrationRequest,StoreWoundDiagnosisRequest,StoreDailyReviewRequest}`,
`Http/Requests/Concerns/ValidatesWoundAssessment`, `Http/Requests/UpdateAssessmentChoiceRequest`,
`Http/Controllers/Admin/AssessmentChoiceController`, `Http/Controllers/ReportController` (CSV),
`Services/ReportingService`, `Services/Reports/*`, `Models/WcAssessmentChoice`.
Tests asserting Arabic medical terms: `AssessmentChoiceAdminTest`, `WoundAssessmentModelTest`,
`MonthlyCaseReportTest`, `MonthlyCaseSummaryTest`, `MonthlyReportExportTest` (and `StructuredWoundTestCase` labels).

## Verification (2026-10-19)

- Full suite on SQLite: 147 tests, 146 passed, 1 skipped (MySQL-only). On MySQL 8.4, one fresh database per file:
  `ClinicalTermsDisplayTest`, `AssessmentChoiceAdminTest`, `WoundAssessmentHistoryTest`, `PatientMedicalRecordTest`,
  `StructuredInitialAssessmentTest` and the four monthly report test files — 58 tests, all passed.
- Found and fixed while doing US2/US3 (both block the agreed behaviour):
  1. `lang/ar/validation.php` did not exist, so built-in messages showed raw keys (`validation.required`); added the
     Arabic messages for the rules the project uses, which FR-005 needs.
  2. The patient history report compared `WC_DREV_OVERALL_STATUS` codes with outcome group names, so every review was
     labelled as a death; it now shows each review's own outcome from the glossary (screen and CSV).
- Not changed: `registrations/create.blade.php` is not reachable from any route or controller (legacy page).
- Gender stays Arabic in the patient demographics banner (demographic, not a medical term); the monthly report
  shows `Male`/`Female` as the workbook does.
