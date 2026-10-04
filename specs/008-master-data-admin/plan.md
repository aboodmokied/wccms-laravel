# Implementation Plan: Master Data Administration

**Branch**: `008-master-data-admin` | **Date**: 2026-10-04 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/008-master-data-admin/spec.md`

---

## Summary

Implement Admin-only (`role:ADMIN`) RESTful master data management across 7 reference domains (Departments, PMH, Diagnoses, Wound Types, Dressings, Medications, Procedures). Enable creating new items, updating item names, and toggling active status (`IS_ACTIVE` 1 ↔ 0) while strictly prohibiting hard deletion (`DELETE`) to maintain historical clinical record integrity.

---

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 11+  
**Primary Dependencies**: Bootstrap 5 (RTL Arabic styling), Laravel Blade, Eloquent, JWT Auth Middleware  
**Storage**: MySQL (`wccms`) local dev database mapping to DB-First `*_tb` reference tables  
**Testing**: PHPUnit / Laravel Feature Testing (`php artisan test --filter=MasterDataAdminTest`)  
**Target Platform**: Web application (WCCMS Clinic Portal)  
**Project Type**: Laravel Web Application  
**Performance Goals**: Sub-2-second index load, search, creation, and status toggle  
**Constraints**: Disallow hard deletes (`DELETE FROM *_tb`); strictly enforce `role:ADMIN` RBAC  
**Scale/Scope**: 7 master data reference tables, Admin workspace  

---

## Constitution Check

*GATE: Passed prior to research and post-design verification.*

1. **Stack Assumption**: Standard Laravel + MySQL (abstracted schema for future PL/SQL port) -> **PASS**
2. **Database-First Architecture & Stored Procedures**: Master data CRUD is explicitly exempt from the stored procedure mandate (Constitution Principle II) because reference table changes carry no clinical side effects -> **PASS**
3. **Database Naming Protocol**: Uses pre-existing `wc_*_tb` tables, `*_CODE` CHAR(36) UUID primary keys, and uppercase columns -> **PASS**
4. **First-Class Arabic & RTL Support**: Built with RTL Arabic layouts, badges, and localization strings -> **PASS**
5. **Actor vs. Data-Entry Separation**: Admin actions authenticated via session/JWT user context -> **PASS**

---

## Project Structure

### Documentation (this feature)

```text
specs/008-master-data-admin/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   └── master-data-api.md
└── checklists/
    └── requirements.md
```

### Source Code Layout

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       ├── DepartmentController.php
│   │       ├── PmhController.php
│   │       ├── DiagnosisController.php
│   │       ├── WoundTypeController.php
│   │       ├── DressingController.php
│   │       ├── MedicationController.php
│   │       └── ProcedureController.php
│   └── Requests/
│       └── StoreMasterDataRequest.php
├── Models/
│   ├── WcDepartment.php
│   ├── WcPmh.php
│   ├── WcDiagnosis.php
│   ├── WcWoundType.php
│   ├── WcDressing.php
│   ├── WcMedication.php
│   └── WcProcedure.php

resources/views/
└── admin/
    ├── index.blade.php
    └── master_data/
        ├── index.blade.php
        └── partials/
            ├── domain-nav.blade.php
            ├── item-modal.blade.php
            └── item-table.blade.php

routes/
└── web.php

tests/
└── Feature/
    └── MasterDataAdminTest.php
```

**Structure Decision**: Standard Laravel single web application structure under `app/Http/Controllers/Admin/`, `resources/views/admin/master_data/`, and `tests/Feature/`.

---

## Complexity Tracking

> **No constitution violations.** Simple reference data management is explicitly exempt from stored procedure mandates per Constitution Principle II.
