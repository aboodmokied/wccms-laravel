# Implementation Plan: Reporting & Dashboards (Phase 9)

**Branch**: `009-reports-dashboards` | **Date**: 2026-10-04 | **Spec**: [spec.md](file:///e:/wamp64/www/wccms/specs/009-reports-dashboards/spec.md)

**Input**: Feature specification from `/specs/009-reports-dashboards/spec.md`

## Summary

Phase 9 implements real-time executive dashboard KPIs and comprehensive patient clinical history reports with CSV export capabilities. Aggregate metrics (active patients, average treatment duration, outcome distribution, missed appointment rates) will be computed using set-based SQL queries adhering strictly to DB-First architecture (Constitution Principle II & VI).

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 11

**Primary Dependencies**: Bootstrap 5, FontAwesome / Bootstrap Icons, Symfony StreamedResponse (CSV)

**Storage**: MySQL 8.0 (Local Development, prepared for Phase 10 Oracle PL/SQL port)

**Testing**: PHPUnit feature test suite (`tests/Feature/ReportsAndDashboardTest.php`)

**Target Platform**: Web Browsers (RTL Arabic First-Class)

**Project Type**: Laravel Web Application

**Performance Goals**: Dashboard KPI calculation and render < 1.0 second; Patient clinical history search < 1.5 seconds

**Constraints**: Must use set-based SQL aggregation logic to support Oracle port in Phase 10; zero external chart dependencies required for Phase 9 MVP.

**Scale/Scope**: Dashboard Controller, Report Controller, CSV Streamer, updated Blade layouts (`dashboard.blade.php`, `reports/history.blade.php`).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Principle I (Stack Assumption)**: Verified — Laravel PHP + MySQL/Oracle.
- **Principle II (Database-First Architecture)**: Verified — Aggregations use DB queries/views ready for Oracle PL/SQL stored procedure port in Phase 10.
- **Principle III (Unified Naming Protocol)**: Verified — References existing tables (`WC_REGISTRATION_TB`, `WC_DAILY_REVIEW_TB`, etc.).
- **Principle IV (Arabic & RTL Support)**: Verified — First-class Arabic UI and RTL formatting across dashboard cards and reports.
- **Principle V (Actor vs. Data-Entry)**: Verified — Displays clinician nurse (`WC_REG_NURSE_CD`) and recording user (`WC_REG_CREATED_BY_CD`) distinctly in clinical timeline.
- **Principle VI (MySQL-First, Oracle-Later)**: Verified — Aggregations implemented using Eloquent DB query builder, avoiding driver-specific code.

*Result*: **PASSED ALL GATES**.

## Project Structure

### Documentation (this feature)

```text
specs/009-reports-dashboards/
├── plan.md              # Implementation Plan
├── research.md          # Phase 0 Research
├── data-model.md        # Phase 1 Data Model & KPI Aggregations
├── quickstart.md        # End-to-End Validation Guide
└── contracts/           # API & Route Contracts
    └── reports-api.md
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php   # Dashboard KPI controller
│   │   └── ReportController.php      # Patient history search & CSV export
│   └── Services/
│       └── ReportingService.php      # DB aggregation service logic
resources/
└── views/
    ├── dashboard.blade.php           # Executive KPI dashboard
    └── reports/
        └── history.blade.php         # Patient history timeline & CSV export UI
tests/
└── Feature/
    └── ReportsAndDashboardTest.php   # PHPUnit integration test
```

**Structure Decision**: Standard Laravel web application structure leveraging `DashboardController`, `ReportController`, and `ReportingService`.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | Fully compliant with architecture guidelines |
