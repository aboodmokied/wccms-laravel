# Quickstart & Runnable Validation Guide: Phase 1 — Core Data Model

## Validation Overview

This guide provides runnable instructions to verify that the Phase 1 database migrations, seeders, and Eloquent models pass all quality gates and conform to the Database-First architecture.

---

## Prerequisites

- **PHP**: 8.3 or higher installed
- **Composer**: Dependencies installed (`composer install`)
- **Database**: MySQL `wccms` database configured in `.env` (`DB_CONNECTION=mysql`, `DB_DATABASE=wccms`)

---

## Runnable Scenarios

### Scenario 1: Fresh Database Migration & Seeding
Runs all 16 table migrations and populates reference seeders:

```bash
# Execute fresh migrations and seeders
php artisan migrate:fresh --seed
```

**Expected Outcome**:
- 16 tables created successfully in `wccms` MySQL database.
- 7 master reference tables (`WC_DEPARTMENT_TB`, `WC_PMH_TB`, `WC_DIAGNOSIS_TB`, `WC_WOUND_TYPE_TB`, `WC_DRESSING_TB`, `WC_MEDICATION_TB`, `WC_PROCEDURE_TB`) contain active records.

### Scenario 2: Verification of Database Naming Protocol & Model Mapping
Executes PHPUnit feature and unit test suite verifying Eloquent models:

```bash
# Run unit and feature tests for Phase 1
php artisan test --filter=CoreDataModelTest
```

**Expected Outcome**:
- All test assertions pass with zero failures.
- Verifies `$primaryKey`, `$incrementing = false`, `$keyType = 'string'`, and `$timestamps = false` across all models.

### Scenario 3: Code Style Verification
Runs Laravel Pint to verify formatting against project style rules:

```bash
# Run Pint code style formatter
vendor/bin/pint --format agent
```

**Expected Outcome**:
- All modified PHP files pass Pint code formatting without style errors.
