# Implementation Plan: Phase 6 — Daily Review

**Branch**: `006-daily-review` | **Date**: 2026-09-29 | **Spec**: [specs/006-daily-review/spec.md](file:///e:/wamp64/www/wccms/specs/006-daily-review/spec.md)

**Input**: Feature specification from `/specs/006-daily-review/spec.md`

---

## Summary

Phase 6 implements the Daily Review (Follow-up Visits) module for the Wound Care Clinic Management System (WCCMS). Building upon active registrations from Phase 5, this phase provides:
1. Daily review workspace per registration (`/registrations/{registration}/daily-reviews`), displaying read-only patient summary card, active wound diagnosis tabs (`WC_REG_DIAGNOSIS_TB`), and chronological daily review history timeline.
2. Multi-wound daily follow-up visit entry form/modal creating `WC_DAILY_REVIEW_TB` (visit header) and `WC_DREVD_DETAIL_TB` (per-wound details) records.
3. Support for per-wound observations, dressing updates (`WC_DREVD_DRESSING_CD`), medication updates (`WC_DREVD_MEDICATION_CD`), and per-wound outcome status (`IMPROVED_DISCHARGE`, `IMPROVED_FOLLOWUP`, `WORSENED`, `DECEASED`).
4. Support for visit-level overall outcome status (`WC_DREV_OVERALL_STATUS`: `IMPROVED_DISCHARGE`, `IMPROVED_SCHEDULED`, `WORSENED_STAY`, `DEATH`) with dual audit tracking (`WC_DREV_NURSE_CD` vs `WC_DREV_ENTRY_USER_CD`).
5. Deriving current dressing and medication in wound tabs from the latest applicable `wc_daily_review_detail_tb` record for each wound diagnosis.
6. Automatic state transition of registration status to `DISCHARGED` and auto-creation of pending scheduled review records (`WC_SCHEDULED_REVIEW_TB`) when overall outcome `IMPROVED_SCHEDULED` or `IMPROVED_DISCHARGE` is selected.
7. Discharged registration lock preventing new daily reviews while maintaining read-only historical access.
8. Conversion of static HTML template `pre-views/daily-review.html` into integrated Metronic 8 Blade views.

---

## Technical Context

**Language/Version**: PHP 8.3 (Laravel 11.x)  
**Primary Dependencies**: `laravel/framework`, `firebase/php-jwt`, Vite asset pipeline  
**Storage**: MySQL `wccms` DB-First tables (`wc_daily_review_tb`, `wc_daily_review_detail_tb`, `wc_registration_tb`, `wc_reg_diagnosis_tb`, `wc_dressing_tb`, `wc_medication_tb`, `wc_scheduled_review_tb`, `per_employee_tb`, `per_users_tb`)  
**Testing**: PHPUnit / Laravel Feature & Unit test runner (`php artisan test`)  
**Target Platform**: Web (Modern desktop browsers with Arabic RTL layout, Cairo font, Metronic 8 styling)  
**Project Type**: Laravel Web Application  
**Performance Goals**: Page render under 500ms, multi-wound review submission under 300ms  
**Constraints**: 100% Arabic RTL layout, strict DB-First schema preservation, dual audit tracking (`WC_DREV_NURSE_CD` vs `WC_DREV_ENTRY_USER_CD`)  
**Scale/Scope**: Clinical staff users (Nurses, Admins), daily clinic visit records  

---

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Database-First Architecture**: ✅ PASS. Utilizes pre-existing `wc_daily_review_tb`, `wc_daily_review_detail_tb`, and `wc_scheduled_review_tb` tables. Models map directly to `_tb` names, CHAR(36) UUID PKs, `$timestamps = false`.
- **Naming Protocol**: ✅ PASS. Tables use `<PREFIX>_<name>_TB` and columns use UPPERCASE prefixes (`WC_DREV_CODE`, `WC_DREVD_CODE`, etc.).
- **Arabic/RTL Support**: ✅ PASS. UI Blade views enforce `dir="rtl"`, Cairo typography, and Metronic 8 layout components.
- **Actor vs. Data-Entry Separation**: ✅ PASS. Explicitly distinguishes `WC_DREV_NURSE_CD` (attending nurse) from `WC_DREV_ENTRY_USER_CD` (authenticated system user).

---

## Project Structure

### Documentation (this feature)

```text
specs/006-daily-review/
├── spec.md              # Feature specification
├── plan.md              # Implementation plan (/speckit-plan output)
├── research.md          # Research & technical decisions
├── data-model.md        # DB-First entity mapping & relationships
├── quickstart.md        # Runnable verification guide
├── contracts/           # Route & controller API contracts
│   └── daily-review-api.md
└── checklists/
    └── requirements.md  # Specification quality checklist
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── DailyReviewController.php
│   └── Requests/
│       └── StoreDailyReviewRequest.php
└── Models/
    ├── WcDailyReview.php
    ├── WcDailyReviewDetail.php
    ├── WcRegistration.php
    ├── WcRegDiagnosis.php
    ├── WcDressing.php
    ├── WcMedication.php
    └── WcScheduledReview.php

resources/views/
└── reviews/
    ├── daily.blade.php
    └── partials/
        ├── patient_summary.blade.php
        ├── wound_tabs.blade.php
        ├── daily_review_modal.blade.php
        └── review_timeline.blade.php

routes/
└── web.php

tests/Feature/
└── DailyReviewTest.php
```

**Structure Decision**: Standard single-project Laravel structure integrating `DailyReviewController`, `StoreDailyReviewRequest`, Blade views in `resources/views/reviews/`, and PHPUnit feature tests.

---

## Complexity Tracking

*No constitution violations. All structures align with pre-existing DB-First architecture.*
