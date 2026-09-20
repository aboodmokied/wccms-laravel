# Implementation Plan: Phase 1 — Core Data Model

**Branch**: `001-core-data-model` | **Date**: 2026-09-20 | **Spec**: [specs/001-core-data-model/spec.md](file:///e:/wamp64/www/wccms/specs/001-core-data-model/spec.md)

**Input**: Feature specification from `specs/001-core-data-model/spec.md`

## Summary

Phase 1 establishes the core Database-First architecture for WCCMS across **16 database tables**, 16 Eloquent Models, and 7 master reference data seeders. The implementation enforces strict naming conventions (`PREFIX_CODE` PKs, `_CD` FKs, `<short_prefix>_` columns), dual audit tracking (`PER_EMPLOYEE_TB` vs `PER_USERS_TB`), and Oracle migration compatibility without reliance on MySQL native ENUM or AUTO_INCREMENT types.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 11  
**Primary Dependencies**: Laravel Framework 11.x, Eloquent ORM, PHPUnit, Laravel Pint  
**Storage**: MySQL `wccms` (local prototype, configured for Oracle SQL Server migration compatibility)  
**Testing**: PHPUnit (`php artisan test`)  
**Target Platform**: WAMP / Apache / PHP 8.3 Web Server  
**Project Type**: Web Application / Database-First Domain & API Layer  
**Performance Goals**: <3 seconds database seeding, sub-100ms query latency across indexed foreign keys  
**Constraints**: 
- Strict Database-First architecture (`$primaryKey`, `$incrementing = false`, `$keyType = 'string'`, `$timestamps = false`)
- Oracle migration compatibility (UUID `CHAR(36)`, no native ENUM/BOOLEAN)
- Dual audit tracking (`PER_EMPLOYEE_TB` clinical actor vs `PER_USERS_TB` data-entry user)
- Soft-flag archiving only (`CANCELLED`, `VOIDED`, `DISCHARGED`, `IS_ACTIVE`)
**Scale/Scope**: 16 Database tables, 16 Eloquent Models, 7 Master reference seeders

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Gate I: Stack Assumption (Laravel + Oracle/MySQL)** — **PASS**: Local MySQL schema designed to port cleanly to Oracle SQL Server.
- **Gate II: Database-First Architecture & In-Database Logic** — **PASS**: Tables use `<PREFIX>_<name>_TB` naming, custom PKs, and `$timestamps = false`.
- **Gate III: Unified Database Naming Protocol** — **PASS**: `PREFIX_CODE` PKs, `_CD` FKs, `<short_prefix>_` column prefixes enforced across all 16 tables.
- **Gate IV: First-Class Arabic & RTL Support** — **PASS**: All text columns support UTF-8 / Arabic string storage.
- **Gate V: Actor vs. Data-Entry Separation** — **PASS**: All clinical tables enforce separate `PER_EMPLOYEE_TB` and `PER_USERS_TB` foreign keys.

## Project Structure

### Documentation (this feature)

```text
specs/001-core-data-model/
├── spec.md              # Feature specification
├── plan.md              # Implementation plan (this file)
├── research.md          # Technical research & decisions (Phase 0)
├── data-model.md        # Entity & schema details (Phase 1)
├── quickstart.md        # Validation & runnable guide (Phase 1)
├── contracts/           # Interface & schema contracts
│   └── database-schema.md
└── checklists/
    └── requirements.md  # Quality checklist
```

### Source Code Layout

```text
app/
├── Models/
│   ├── PerUser.php
│   ├── PerEmployee.php
│   ├── MrPatient.php
│   ├── WcRequestReg.php
│   ├── WcRegistration.php
│   ├── WcRegDiagnosis.php
│   ├── WcDailyReview.php
│   ├── WcDailyReviewDetail.php
│   ├── WcScheduledReview.php
│   ├── WcDepartment.php
│   ├── WcPmh.php
│   ├── WcDiagnosis.php
│   ├── WcWoundType.php
│   ├── WcDressing.php
│   ├── WcMedication.php
│   └── WcProcedure.php

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php (Legacy ref / updated)
│   └── 2026_09_20_000001_create_wccms_core_tables.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── WcDepartmentSeeder.php
    ├── WcPmhSeeder.php
    ├── WcDiagnosisSeeder.php
    ├── WcWoundTypeSeeder.php
    ├── WcDressingSeeder.php
    ├── WcMedicationSeeder.php
    └── WcProcedureSeeder.php

tests/
├── Feature/
│   └── CoreDataModelTest.php
└── Unit/
    └── EloquentModelMappingTest.php
```

**Structure Decision**: Standard Laravel application layout under `app/Models`, `database/migrations`, `database/seeders`, and `tests/`.

## Complexity Tracking

> *No constitution violations detected; complexity tracking table omitted.*
