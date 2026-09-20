# Research & Technical Decisions: Phase 1 — Core Data Model

## Research Overview

Phase 1 establishes the 16 core database tables, Eloquent models, and reference data seeders for WCCMS. All technical decisions strictly satisfy the Database-First architecture, Oracle portability, and dual audit tracking required by the WCCMS Constitution.

---

## Technical Decisions

### Decision 1: Primary Key Generation & UUID Handling
- **Decision**: Primary keys use `CHAR(36)` columns populated via `Illuminate\Support\Str::uuid()` in Eloquent model creating events or database triggers.
- **Rationale**: MySQL `CHAR(36)` maps seamlessly to Oracle `CHAR(36)` without requiring DB-specific AUTO_INCREMENT sequences.
- **Alternatives Considered**: 
  - *MySQL Auto-increment BIGINT*: Rejected because Oracle handles sequences differently, complicating future migration.
  - *Binary(16) UUIDs*: Rejected due to human readability in raw SQL queries and potential PL/SQL conversion complexity on Oracle.

### Decision 2: Oracle-Compatible Column Data Types
- **Decision**: Avoid MySQL-native `ENUM` and `BOOLEAN` data types in migrations.
  - Use `VARCHAR(20)` / `VARCHAR(30)` for status codes (e.g. `PENDING`, `APPROVED`, `REJECTED`, `VOIDED`).
  - Use `SMALLINT` (1/0) for active/boolean flags (e.g. `PER_USER_IS_ACTIVE`, `WC_DEPT_IS_ACTIVE`).
- **Rationale**: Oracle SQL Server does not support native `ENUM` or `BOOLEAN` column types. Standardizing on `VARCHAR` and `SMALLINT` ensures migrations run unchanged across both engines.
- **Alternatives Considered**:
  - *Laravel native `enum()` schema builder*: Rejected because MySQL creates database-level ENUM types that fail on Oracle.

### Decision 3: Dual Audit Tracking Pattern (Clinical Actor vs. Data-Entry User)
- **Decision**: Every clinical record mutation table contains two distinct foreign keys:
  - Clinical Employee (`..._NURSE_CD` / `..._CLINICAL_EMP_CD`) referencing `PER_EMPLOYEE_TB` (`PER_EMP_CODE`).
  - Data-Entry User (`..._ENTRY_USER_CD`) referencing `PER_USERS_TB` (`PER_USER_CODE`).
- **Rationale**: Mandated by Constitution Principle V. Separates system login identity from the actual medical staff performing patient care.
- **Alternatives Considered**:
  - *Single `user_id` column*: Rejected because staff logging in may be entering data on behalf of an attending clinical nurse.

### Decision 4: Idempotent Database Reference Seeders
- **Decision**: Implement modular seeders using Eloquent `firstOrCreate` methods registered inside `DatabaseSeeder.php`.
- **Rationale**: Ensures `php artisan db:seed` can be executed repeatedly in any environment without generating duplicate primary key or reference violations.
- **Alternatives Considered**:
  - *Direct SQL insert statements in migrations*: Rejected because data seeders should remain distinct from structural schema migrations.

### Decision 5: Database Indexing Strategy
- **Decision**: Explicitly define single-column indexes on all `_CD` foreign keys, status columns (`WC_REQ_STATUS`, `WC_REG_STATUS`, `WC_SREV_STATUS`), and a unique index on `MR_PAT_ID_NO`.
- **Rationale**: Resolved during `/speckit-clarify` session to eliminate full table scans across `CHAR(36)` foreign key joins and frequent queue status queries.
- **Alternatives Considered**:
  - *Unindexed FK columns*: Rejected due to performance degradation as clinical records grow.

---

## Verification Strategy
- Executing `php artisan migrate:fresh --seed` runs all 16 table migrations and 7 master reference seeders cleanly.
- Executing `php artisan test` verifies model relationships and foreign key integrity.
