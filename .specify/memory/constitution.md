<!--
### Sync Impact Report (1.3.0)
- **Version Change**: 1.2.0 → 1.3.0 (MINOR bump: principle IV gains a medical-terminology exception)
- **Modified Principles**: IV. First-Class Arabic & RTL Support — medical terminology (clinical field labels,
  assessment values, clinical outcomes) is displayed in English on every page and is never translated; all other
  user-facing text stays Arabic. Decided by the project owner on 2026-10-19 (spec 014).
- **Added / Removed Sections**: None.

### Sync Impact Report (1.2.0)
- **Version Change**: 1.1.0 → 1.2.0 (MINOR bump: added Principle VI — MySQL-first, Oracle-later development constraint)
- **Modified Principles**:
  - II. Database-First Architecture & In-Database Logic: scoped the stored-procedure mandate to core clinical workflows rather than all mutations; corrected "voiding registrations/reviews" to "discharging registrations" and "voiding pending scheduled reviews" to match the actual data model.
  - V. Actor vs. Data-Entry Separation: corrected example column from the invented `WC_REG_ENTRY_USER_CD` to the actual schema column `WC_REG_CREATED_BY_CD`.
- **Added Principles**: VI. MySQL-First, Oracle-Later Development.
- **Removed Sections**: None.
- **Follow-up TODOs**: Confirm whether 1.0.0 was ever separately ratified before this document's first version; if not, consider renumbering the whole history starting at 1.0.0.
-->

# WCCMS Constitution

## Core Principles

### I. Stack Assumption
The application stack is **Laravel (PHP) + Oracle SQL Server**. Every specification, plan, task, and implementation phase MUST assume this stack without exception.

### II. Database-First Architecture & In-Database Logic (NON-NEGOTIABLE)
The database is the single source of truth and primary business logic engine.
- Core clinical workflows — approving registration requests, creating registrations, recording discharges, creating and completing daily reviews, scheduling reviews, and voiding pending scheduled reviews on re-registration — MUST reside in triggers, stored procedures, and views inside the database.
- Laravel Eloquent models MUST reflect database structures and call the relevant stored procedures/views for these core clinical mutations; Eloquent models MUST NOT replace or bypass database-side logic for them.
- Simple reference/master-data administration (e.g. toggling `IS_ACTIVE` on a dressing or medication type) is exempt from this mandate and may use standard Eloquent CRUD, since it carries no clinical side effects.

### III. Unified Database Naming Protocol
All database objects must strictly adhere to the unified naming reference document:
- **Tables**: Named as `<PREFIX>_<name>_TB` (e.g. `MR_PATIENT_TB`, `PER_EMPLOYEE_TB`, `WC_REGISTRATION_TB`).
- **Columns**: Prefixed with `<short_prefix>_` (e.g. `MR_PAT_`, `PER_EMP_`, `WC_REG_`).
- **Primary Keys**: Custom column named `CODE` with prefix (e.g. `MR_PAT_CODE`, `WC_REG_CODE`).
- **Foreign Keys**: Column names ending with `_CD` (e.g. `WC_REG_PATIENT_CD`, `WC_REG_NURSE_CD`).

### IV. First-Class Arabic & RTL Support
Any user interface or text content MUST support Arabic as a first-class language.
- Layouts must natively support Right-to-Left (RTL) directional rendering.
- User-facing text, error messages, and clinical review interfaces must provide complete Arabic localization.
- **Exception — medical terminology**: clinical field labels (e.g. Diagnosis, Exudate Amount, Wound Bed), assessment
  values (e.g. Granulation, Purulent) and clinical outcomes are displayed in English on every page, form, report and
  export, and are never translated. They render left-to-right inside the RTL layout. Messages that mention them stay
  Arabic (e.g. «حقل Exudate Amount مطلوب.»).

### V. Actor vs. Data-Entry Separation
Any feature or record involving clinical actions ("who performed a clinical action") MUST explicitly distinguish:
- **Clinical Actor**: The actual employee performing the clinical task, referencing `PER_EMPLOYEE_TB` (e.g. `WC_REG_NURSE_CD`).
- **Data-Entry User**: The system user logging into the system and recording the data, referencing `PER_USERS_TB` (e.g. `WC_REG_CREATED_BY_CD`).

### VI. MySQL-First, Oracle-Later Development
Development happens against MySQL locally; the schema is deliberately designed to port cleanly to Oracle later (see Phase 1 and Phase 9 of the project blueprint).
- Migrations MUST use Laravel's fluent schema-builder syntax rather than raw SQL wherever possible, since the builder abstracts most driver differences between MySQL and Oracle.
- Triggers and stored procedures written against MySQL during development are treated as executable specifications of the business logic — they MUST be rewritten by hand in PL/SQL during the Oracle port (Phase 9); they are NOT expected to auto-translate.
- No spec, plan, or task for Phases 1–8 may assume Oracle-only syntax or features; Oracle-specific work is scoped exclusively to Phase 9.

## Quality Gates & Workflow
1. Specification alignment with Database-First stored procedure calls and naming conventions.
2. Verification via PHPUnit feature tests calling real/mocked procedure interfaces.
3. Code style enforcement using Pint (`vendor/bin/pint --format agent`).

## Governance
This constitution supersedes all secondary development guidelines. Any changes to core database logic, architecture, or naming protocols require an amended constitution update and version bump.

**Version**: 1.3.0 | **Ratified**: 2026-09-20 | **Last Amended**: 2026-10-19