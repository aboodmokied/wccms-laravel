# WCCMS Constitution

## Core Principles

### I. Database-First Architecture (NON-NEGOTIABLE)
The pre-existing MySQL database schema (`wccms`) is the primary source of truth. All domain tables end with `_tb` (e.g. `mr_patient_tb`, `wc_registration_tb`).
- Models must explicitly define custom primary keys (`$primaryKey = 'PREFIX_CODE'`), `$incrementing = false`, `$keyType = 'string'`, and `$timestamps = false`.
- Column names in queries and Eloquent relationships must match database definitions exactly.

### II. Laravel Ecosystem Standards
Follow modern Laravel practices (Laravel 11 / PHP 8.3).
- Use Artisan code generators (`php artisan make:*`) for creating components.
- Enforce strict PHP type hints, explicit return declarations, and run Pint formatter (`vendor/bin/pint --format agent`) on code changes.
- Links and navigation must use named routes via `route()`.

### III. Test-Driven & Feature Testing Priority
Maintain continuous test coverage for critical business workflows.
- Create feature tests for endpoints and services using `php artisan make:test`.
- Use model factories for test setup rather than ad-hoc database mutations in tests.

### IV. Defensive Security & Data Integrity
Healthcare and patient record integrity is critical.
- Authorization checks must be enforced via policies and gates.
- Inputs must be validated through Form Requests before mutating database state.

## Tech Stack & Environment
- **Framework**: Laravel 11 / PHP 8.3
- **Database**: MySQL (`wccms`) - DB-First Pattern
- **Styling & Assets**: Tailwind CSS & Vite

## Quality Gates & Workflow
1. Code formatting check with Pint (`vendor/bin/pint --format agent`).
2. Verification via PHPUnit feature tests (`php artisan test`).
3. Spec-driven development using GitHub Spec Kit workflows (`.specify/`).

**Version**: 1.0.0 | **Ratified**: 2026-09-20 | **Last Amended**: 2026-09-20

