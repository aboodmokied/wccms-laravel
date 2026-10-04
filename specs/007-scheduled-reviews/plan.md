# Implementation Plan: Phase 7 — Scheduled Reviews & Appointments Management

**Branch**: `007-scheduled-reviews` | **Date**: 2026-10-03 | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/007-scheduled-reviews/spec.md)

**Input**: Feature specification from `/specs/007-scheduled-reviews/spec.md`

## Summary

Implement the Scheduled Reviews & Appointments Management module (Phase 7) for the Wound Care Clinic Management System (WCCMS). This feature encompasses:
1. Scheduled Review Dashboard & Queue (`/reviews/scheduled`) with summary stats (Today, Upcoming, Overdue, Completed, Cancelled) and dynamic filtering by status, search query, and date range.
2. Appointment Fulfillment via Daily Review Visits: Nurses launch visits from pending scheduled appointments, which pre-fills evaluation context and automatically transitions appointment status to `COMPLETED` while setting `WC_DREV_SCHEDULED_REVIEW_CD`.
3. Manual Appointment Management: Rescheduling target dates and manually cancelling appointments with cancellation notes. Pending appointments stay pending indefinitely until staff act.
4. Hard Deletion on Re-Registration: When a new active registration is created for a patient, all `PENDING` scheduled review records from their previous registrations are automatically hard-deleted from `wc_scheduled_review_tb`.

The feature strictly adheres to WCCMS Database-First conventions (`_tb` tables, CHAR(36) UUIDs, custom primary key definitions), complete Arabic RTL UI, and actor/data-entry audit separation.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 11

**Primary Dependencies**: Laravel Framework 11, Bootstrap 5 (RTL enabled), FontAwesome 6 Icons

**Storage**: MySQL (`wccms` database, `wc_scheduled_review_tb`, `wc_daily_review_tb`, `wc_registration_tb`, `mr_patient_tb`)

**Testing**: PHPUnit Feature Tests (`php artisan test --filter=ScheduledReviewTest`)

**Target Platform**: Web Browser (Desktop & Tablet / WAMP / Apache / PHP 8.3)

**Project Type**: Laravel Web Application (Blade MVC + Eloquent DB-First Models)

**Performance Goals**: Dashboard rendering and search filtering < 2 seconds; atomic execution of appointment fulfillment and re-registration cleanup transactions.

**Constraints**:
- Database-First architecture (`$table = 'wc_scheduled_review_tb'`, `$primaryKey = 'WC_SREV_CODE'`, `$incrementing = false`, `$keyType = 'string'`, `$timestamps = false`).
- Dual audit tracking: clinical nurse (`PER_EMPLOYEE_TB`) vs data-entry user (`PER_USERS_TB`).
- Zero automated status background transitions: `PENDING` appointments remain pending indefinitely until manually completed/cancelled or hard-deleted on re-registration.
- Arabic-first RTL UI styling and messaging.

**Scale/Scope**: Clinic appointment queue management, single and multi-patient review workflows.

## Constitution Check

*GATE: Passed prior to Phase 0 research & Phase 1 design.*

- **Principle I: Stack Assumption** — PHP 8.3 + Laravel 11 + MySQL (Oracle-compatible schema). **PASS**
- **Principle II: Database-First Architecture & In-Database Logic** — All models map to existing `_tb` tables (`wc_scheduled_review_tb`, `wc_daily_review_tb`). Critical workflows (fulfillment linkage, re-registration cleanup) run inside atomic DB transactions. **PASS**
- **Principle III: Unified Database Naming Protocol** — Column names use uppercase prefixes (`WC_SREV_CODE`, `WC_SREV_REGISTRATION_CD`, `WC_SREV_SCHEDULED_DATE`, `WC_SREV_STATUS`, `WC_SREV_NOTES`, `WC_SREV_CREATED_BY_CD`, `WC_SREV_CREATED_ON`, `WC_SREV_COMPLETED_ON`). **PASS**
- **Principle IV: First-Class Arabic & RTL Support** — All UI views, badges, labels, modals, and flash messages provide native Arabic RTL support. **PASS**
- **Principle V: Actor vs. Data-Entry Separation** — Separates clinical nurse (`WC_DREV_NURSE_CD` -> `PER_EMPLOYEE_TB`) from system user (`WC_DREV_ENTRY_USER_CD` / `WC_SREV_CREATED_BY_CD` -> `PER_USERS_TB`). **PASS**
- **Principle VI: MySQL-First, Oracle-Later Development** — Standard Eloquent queries and DB transaction hooks used without MySQL or Oracle driver vendor lock-in. **PASS**

## Project Structure

### Documentation (this feature)

```text
specs/007-scheduled-reviews/
├── spec.md              # Feature specification
├── plan.md              # Implementation plan (this file)
├── research.md          # Technical research & design decisions
├── data-model.md        # Data entities, relationships & state transitions
├── quickstart.md        # Validation scenarios & test suite instructions
└── contracts/
    └── scheduled-review-api.md  # Web route & controller specifications
```

### Source Code (repository root)

```text
app/
├── Http/
│   └── Controllers/
│       ├── ScheduledReviewController.php   # Main dashboard, reschedule, cancel, fulfill handlers
│       ├── DailyReviewController.php       # Updated to handle appointment linkage on store
│       └── RegistrationRequestController.php # Updated to hard-delete old pending reviews on accept
├── Models/
│   ├── WcScheduledReview.php              # Eloquent model for wc_scheduled_review_tb
│   ├── WcDailyReview.php                  # Eloquent model with scheduled review relationship
│   └── WcRegistration.php                 # Eloquent model with scheduled review relationships
└── Services/
    └── ScheduledReviewService.php         # Business logic service for appointment status updates & cleanup

resources/views/
└── reviews/
    ├── scheduled.blade.php                 # Dashboard view (summary ribbon, tabs, queue table)
    └── partials/
        ├── reschedule-modal.blade.php      # Reschedule appointment modal dialog
        └── cancel-modal.blade.php          # Cancel appointment modal dialog

routes/
└── web.php                                 # Registered routes for /reviews/scheduled/*

tests/
└── Feature/
    └── ScheduledReviewTest.php             # Comprehensive PHPUnit feature test suite
```

**Structure Decision**: Standard Laravel MVC architecture adhering to DB-First rules, leveraging dedicated Eloquent models and a controller/service pattern for business transactions.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

