# Implementation Plan: Phase 4 — Registration Requests

**Branch**: `004-registration-requests` | **Date**: 2026-09-23 | **Spec**: [`spec.md`](spec.md)

**Input**: Feature specification from [`specs/004-registration-requests/spec.md`](spec.md)

## Summary

Phase 4 implements the Hospital Ward Referral and Wound Care Clinic Registration Request workflow in WCCMS without modifying the existing database schema. Ward nurses search patient records, view admission context, and submit referral requests (`wc_request_reg_tb`). Clinic nurses handle incoming queue items, conduct ward visits (`VISIT_IN_PROGRESS`), and accept patients (`ACCEPTED`), which automatically invokes an in-database procedure to instantiate active registrations (`wc_registration_tb`) with the default clinic department code (`WC_DEPT_CODE`). All views adhere to 100% Arabic RTL layout.

## Technical Context

**Language/Version**: PHP 8.3 / Laravel 11
**Primary Dependencies**: Vite, Blade Components, Bootstrap 5 (RTL bundle)
**Storage**: MySQL (Local Dev) / Oracle PL/SQL (Target Production)
**Testing**: PHPUnit / Laravel Feature & Unit Tests
**Target Platform**: Web (Responsive Desktop & Mobile Ward Tablets)
**Project Type**: Laravel Web Application
**Performance Goals**: <500ms page load for request queues, <45s average referral submission time
**Constraints**: 0 DB schema migrations/column changes; strict adherence to Database-First Architecture (Constitution Principle II); 100% Arabic RTL compliance
**Scale/Scope**: ~100 daily referral requests across hospital wards

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Principle I: Stack Assumption**: Passes. PHP 8.3 / Laravel + MySQL/Oracle.
- **Principle II: Database-First Architecture & In-Database Logic**: Passes. `sp_accept_registration_request` procedure performs the core registration creation mutation. Eloquent models reflect structure and execute the DB procedure.
- **Principle III: Unified Database Naming Protocol**: Passes. Uses `wc_request_reg_tb` and `wc_registration_tb` with PK/FK naming rules (`WC_REQ_CODE`, `WC_REQ_PATIENT_CD`, etc.).
- **Principle IV: First-Class Arabic & RTL Support**: Passes. Blade templates use Arabic UI strings and RTL Bootstrap styling.
- **Principle V: Actor vs. Data-Entry Separation**: Passes. Distinguishes ward nurse (`WC_REQ_NURSE_CD`), clinic nurse (`WC_REQ_REVIEWED_BY_CD`), and entry user (`WC_REQ_ENTRY_USER_CD`).
- **Principle VI: MySQL-First, Oracle-Later Development**: Passes. Schema builder / MySQL stored procedure written as executable spec.

## Project Structure

### Documentation (this feature)

```text
specs/004-registration-requests/
├── plan.md              # Implementation plan (this document)
├── research.md          # Phase 0 architectural & workflow decisions
├── data-model.md        # Phase 1 entity definitions, relationships & DB procedures
├── quickstart.md        # Phase 1 runnable validation scenarios & test guide
└── contracts/           # Phase 1 Web & API endpoint contracts
    └── requests-api.md  # Endpoints specification
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── RegistrationRequestController.php
│   └── Requests/
│       ├── StoreRegistrationRequest.php
│       └── MarkIneligibleRequest.php
├── Models/
│   ├── WcRequestReg.php
│   └── WcRegistration.php
└── Services/
    └── PatientSyncService.php

database/
└── migrations/
    └── (Existing 2026_09_20_000001_create_wccms_core_tables.php utilized - 0 new migrations)

resources/
├── views/
│   └── requests/
│       ├── create.blade.php
│       ├── index.blade.php
│       ├── my_requests.blade.php
│       └── show.blade.php
└── css/js/

tests/
├── Feature/
│   ├── RegistrationRequestTest.php
│   └── AcceptRegistrationRequestProcedureTest.php
```

**Structure Decision**: Standard Laravel Single Project structure.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | Fully compliant with project constitution and schema constraints |
