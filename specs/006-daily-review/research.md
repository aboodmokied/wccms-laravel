# Research & Technical Decisions: Phase 6 — Daily Review

## 1. Database-First Schema Mapping for Daily Review

### Decision
Use pre-existing `wc_daily_review_tb` and `wc_daily_review_detail_tb` tables created in Phase 1 (`2026_09_20_000001_create_wccms_core_tables.php`).

### Rationale
- `wc_daily_review_tb` acts as the visit header record (`WC_DREV_CODE`, `WC_DREV_REGISTRATION_CD`, `WC_DREV_SCHEDULED_REVIEW_CD`, `WC_DREV_VISIT_DATE`, `WC_DREV_NURSE_CD`, `WC_DREV_ENTRY_USER_CD`, `WC_DREV_OVERALL_STATUS`, `WC_DREV_GENERAL_NOTES`).
- `wc_daily_review_detail_tb` acts as the per-wound evaluation record (`WC_DREVD_CODE`, `WC_DREVD_DAILY_REVIEW_CD`, `WC_DREVD_REG_DIAGNOSIS_CD`, `WC_DREVD_DRESSING_CD`, `WC_DREVD_MEDICATION_CD`, `WC_DREVD_OBSERVATION`, `WC_DREVD_OUTCOME_STATUS`).
- Aligns 100% with the DB-First architecture rules in `.ai/rules/db-first.md`.

---

## 2. Multi-Wound Atomic Form Transaction Handling

### Decision
Execute daily review creation inside a database transaction (`DB::transaction(...)`).

### Rationale
1. Generate UUID for `WC_DREV_CODE` header and insert `wc_daily_review_tb` record.
2. Iterate through submitted wound diagnoses array and insert corresponding `wc_daily_review_detail_tb` records.
3. If visit-level overall outcome status (`WC_DREV_OVERALL_STATUS`) is `IMPROVED_SCHEDULED` or `IMPROVED_DISCHARGE`:
   - Update `wc_registration_tb` status to `DISCHARGED` and `WC_REG_DISCHARGED_AT = now()`, `WC_REG_DISCHARGED_BY_EMP_CD = $nurseCd`.
   - If `IMPROVED_SCHEDULED`, create pending `wc_scheduled_review_tb` appointment record and link `WC_DREV_SCHEDULED_REVIEW_CD`.
4. Wrapping all steps in a transaction ensures atomic integrity—preventing orphan review headers or partial wound entries.

---

## 3. Metronic 8 Arabic RTL Modal & Accordion View Architecture

### Decision
Convert `pre-views/daily-review.html` into a modular Blade view `resources/views/reviews/daily.blade.php` supported by Blade partials:
- `resources/views/reviews/partials/patient_summary.blade.php`: Demographics & hospital context.
- `resources/views/reviews/partials/wound_tabs.blade.php`: Active wound diagnosis tab bar.
- `resources/views/reviews/partials/daily_review_modal.blade.php`: Multi-wound evaluation modal with diagnosis accordion sections.
- `resources/views/reviews/partials/review_timeline.blade.php`: Collapsible historical daily review timeline cards.

### Rationale
- Reuses Metronic 8 layout (`layouts.app`), Cairo Arabic typography, and RTL Bootstrap styles established in Phase 2 & 5.
- Keeps Blade views modular, clean, and maintainable.

---

## 4. Dual Audit Logging (Attending Nurse vs Data Entry User)

### Decision
- `WC_DREV_NURSE_CD` stores the employee ID (`PER_EMP_CODE`) selected from the clinical nurse dropdown.
- `WC_DREV_ENTRY_USER_CD` automatically records the currently authenticated user's ID (`PER_USER_CODE` / `auth()->user()->PER_USER_CODE`).

### Rationale
Strictly enforces the project's constitutional principle of distinguishing the clinical actor who performed the evaluation from the system user who logged into WCCMS to record the data.
